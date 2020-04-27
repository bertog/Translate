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
        if (in_array($key, $this->translatable)) {

            $translation = $this->getTranslationByField($key);

            return $translation ? $translation->text : parent::getAttribute($key);
        }

        return parent::getAttribute($key);
    }

    public function updateOrCreateTranslation($array)
    {
        $payload = collect($array)->mapWithKeys(function ($value, $key) {

            if (!in_array($key, $this->translatable)) throw new AttributeNotTranslatableException();

            return [
                'lang' => App::getLocale(),
                'field' => $key,
                'text' => $value,
            ];
        });

        $translation = $this->getTranslationByField(key($array));

        if (!$translation) {
            return $this->translations()->create($payload->toArray());
        }

        return $translation->update($payload->toArray());
    }

    public function storeMultipleTranslations($translations)
    {
        $translationsGroup = collect($translations);

        $translationsGroup->each(function ($translation, $field)  {

            if (! in_array($field, $this->translatable)) throw new AttributeNotTranslatableException();

            $singleTranslation = collect($translation);

            $singleTranslation->each(function ($text, $lang) use ($field) {
                $this->translations()->create([
                    'lang' => $lang,
                    'field' => $field,
                    'text' => $text,
                ]);
            });
        });
    }

    protected function getTranslationByField($field)
    {
        return $this->translations()->currentLang()->whereField($field)->first();
    }

    protected function getTranslationByLang()
    {
        return $this->translations()->currentLang()->get();
    }


    public function toArray()
    {
        $array = parent::toArray();

        $translations = $this->getTranslationByLang();

        foreach ($this->translatable as $key) {
            foreach ($translations as $translation) {
                if ($translation->field == $key) {
                    $array[$key] = $translation->text;
                }
            }
        }

        return $array;

    }

}