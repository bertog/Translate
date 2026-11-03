<?php

namespace TheNonsenseFactory\Translate\Traits;

use Illuminate\Support\Facades\App;
use TheNonsenseFactory\Translate\Exceptions\AttributeNotTranslatableException;
use TheNonsenseFactory\Translate\Models\Translation;

trait Translatable
{
    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function getAttribute($key)
    {
        if (in_array($key, $this->getTranslatableAttributes(), true)) {
            $translation = $this->getTranslationForCurrentLocale($key);

            return $translation ? $translation->text : parent::getAttribute($key);
        }

        return parent::getAttribute($key);
    }

    /**
     * Create or update, in the current App locale, the translations for the given fields.
     *
     * Accepts an array shaped as field => text. With a single field it returns the
     * created Translation or the boolean result of the update, as it always did.
     * With more fields it returns the result for the last one.
     */
    public function updateOrCreateTranslation($array)
    {
        $array = (array) $array;

        $this->ensureFieldsAreTranslatable(array_keys($array));

        $result = null;

        foreach ($array as $field => $text) {
            $payload = [
                'lang' => App::getLocale(),
                'field' => $field,
                'text' => $text,
            ];

            $translation = $this->getTranslationByField($field);

            $result = $translation
                ? $translation->update($payload)
                : $this->translations()->create($payload);
        }

        $this->forgetLoadedTranslations();

        return $result;
    }

    /**
     * Store translations shaped as field => [lang => text], updating the existing ones.
     */
    public function storeMultipleTranslations($translations)
    {
        $translations = collect($translations);

        $this->ensureFieldsAreTranslatable($translations->keys()->all());

        $this->getConnection()->transaction(function () use ($translations) {
            $translations->each(function ($texts, $field) {
                collect($texts)->each(function ($text, $lang) use ($field) {
                    $attributes = ['lang' => $lang, 'field' => $field];

                    if ($this->checkTranslation($attributes)) {
                        $this->translations()->where($attributes)->update(['text' => $text]);

                        return;
                    }

                    $this->translations()->create($attributes + ['text' => $text]);
                });
            });
        });

        $this->forgetLoadedTranslations();
    }

    public function createOrUpdateMultipleTranslations($translation)
    {
        $this->storeMultipleTranslations($translation);
    }

    protected function getTranslationByField($field)
    {
        return $this->translations()->where([
            'field' => $field,
            'lang' => App::getLocale(),
        ])->first();
    }

    protected function getTranslationByLang()
    {
        return $this->translations->filter(function ($translation) {
            return $translation->lang == App::getLocale();
        });
    }

    /**
     * Read a translation from the loaded relation when available (eager loading),
     * otherwise fall back to a query.
     */
    protected function getTranslationForCurrentLocale($field)
    {
        if (! $this->relationLoaded('translations')) {
            return $this->getTranslationByField($field);
        }

        $locale = App::getLocale();

        return $this->getRelation('translations')->first(function ($translation) use ($field, $locale) {
            return $translation->field == $field && $translation->lang == $locale;
        });
    }

    protected function getTranslatableAttributes()
    {
        // property_exists avoids going through __get, which would call getAttribute() again.
        return property_exists($this, 'translatable') && is_array($this->translatable)
            ? $this->translatable
            : [];
    }

    protected function ensureFieldsAreTranslatable(array $fields)
    {
        $translatable = $this->getTranslatableAttributes();

        foreach ($fields as $field) {
            if (! in_array($field, $translatable, true)) {
                throw new AttributeNotTranslatableException();
            }
        }
    }

    protected function forgetLoadedTranslations()
    {
        if ($this->relationLoaded('translations')) {
            $this->unsetRelation('translations');
        }
    }

    public function checkTranslation(Array $translation)
    {
        return $this->translations()->where($translation)->exists();
    }

    public function toArray()
    {
        $array = parent::toArray();

        $translatable = $this->getTranslatableAttributes();

        if (empty($translatable)) {
            return $array;
        }

        foreach ($this->getTranslationByLang() as $translation) {
            if (in_array($translation->field, $translatable, true)) {
                $array[$translation->field] = $translation->text;
            }
        }

        return $array;
    }
}
