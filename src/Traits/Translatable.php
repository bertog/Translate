<?php


namespace TheNonsenseFactory\Translate\Traits {


    use Illuminate\Support\Arr;
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

                if (! in_array($key, $this->translatable)) throw new AttributeNotTranslatableException();

                return [
                    'lang' => App::getLocale(),
                    'field' => $key,
                    'text' => $value,
                ];
            });

            $translation = $this->getTranslationByField(key($array));

            if (! $translation) {
                return $this->translations()->create($payload->toArray());
            }

            return $translation->update($payload->toArray());
        }

        protected function getTranslationByField($field) {
            return $this->translations()->currentLang()->whereField($field)->first();
        }

        protected function getTranslationByLang() {
            return $this->translations()->currentLang()->get();
        }


        public function toArray()
        {
            $array = parent::toArray();

            foreach ($this->translatable as $key)
            {
                if (array_key_exists($key, $array)) {
                    $array[$key] = $this->{$key};
                }
            }
            return $array;
        }
    }
}