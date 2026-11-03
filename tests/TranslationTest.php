<?php

namespace TheNonsenseFactory\Translate\Tests;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use TheNonsenseFactory\Translate\Exceptions\AttributeNotTranslatableException;
use TheNonsenseFactory\Translate\Models\Translation;
use TheNonsenseFactory\Translate\Tests\Models\Note;
use TheNonsenseFactory\Translate\Tests\Models\Post;


class TranslationTest extends TestCase
{

    public function test_it_can_save_a_translation_for_a_model()
    {
        $post = Post::create([
           'title' => 'Title',
           'body' => 'Body,'
        ]);

        $post->translations()->create([
            'lang' => 'it',
            'text' => 'Titolo in italiano',
            'field' => 'title'
        ]);

        $this->assertDatabaseHas('translations', [
            'lang' => 'it',
            'field' => 'title',
            'text' => 'Titolo in italiano',
            'translatable_id' => $post->id,
            'translatable_type' => 'TheNonsenseFactory\Translate\Tests\Models\Post'
        ]);
    }

    public function test_it_can_get_the_translation_in_a_specific_lang()
    {
        $post = Post::create([
            'title' => 'Title',
            'body' => 'Body,'
        ]);

        $post->translations()->create([
            'lang' => 'it',
            'field' => 'title',
            'text' => 'Titolo in Italiano'
        ]);

        $post->translations()->create([
            'lang' => 'en',
            'field' => 'title',
            'text' => 'Title in English'
        ]);

        $this->assertCount(2, $post->translations);

        App::setLocale('en');
        $en = $post->translations()->currentLang()->where('field', 'title')->first();

        App::setLocale('it');
        $it = $post->translations()->currentLang()->whereField('title')->first();

        $this->assertEquals('Title in English', $en->text);
        $this->assertEquals('Titolo in Italiano', $it->text);

    }

    public function test_item_description_follow_the_App_Localisation()
    {
        $post = Post::create([
            'title' => 'Title',
            'body' => 'Body,'
        ]);

        $body = $post->body;

        $post->translations()->create([
            'lang' => 'it',
            'field' => 'title',
            'text' => 'Titolo in Italiano'
        ]);

        $post->translations()->create([
            'lang' => 'en',
            'text' => 'Title in English',
            'field' => 'title',
        ]);

        App::setLocale('it');

        $this->assertEquals('Titolo in Italiano', $post->title);
        $this->assertEquals($body, $post->body);

        App::setLocale('en');

        $this->assertEquals('Title in English', $post->title);
        $this->assertEquals($body, $post->body);
    }

    public function test_if_the_description_in_the_specified_language_does_not_exists_provide_the_fallback()
    {
        $title = 'Titolo in Italiano';

        $post = Post::create([
            'title' => $title,
            'body' => 'Body,'
        ]);

        App::setLocale('en');

        $this->assertEquals($title, $post->title);

    }

    public function test_it_create_if_not_present_a_translation()
    {
        //The translation is not present
        $post = Post::create([
            'title' => 'Titolo',
            'body' => 'Body,'
        ]);

        $title = ['title' => 'Questo Titolo non Esiste'];

        App::setLocale('it');

        $post->updateOrCreateTranslation($title);

        $this->assertEquals($title['title'], $post->title);

    }

    public function test_it_update_a_traslation_if_is_present()
    {
        $post = Post::create([
            'title' => 'Titolo',
            'body' => 'Body,'
        ]);

        App::setLocale('it');

        $post->updateOrCreateTranslation(['title' => 'Titolo in Italiano']);
        $post->updateOrCreateTranslation(['body' => 'Body in Italiano']);

        $this->assertEquals('Titolo in Italiano', $post->title);
        $this->assertEquals('Body in Italiano', $post->body);

        $post->updateOrCreateTranslation(['title' => 'Titolo Aggiornato']);

        $this->assertEquals('Titolo Aggiornato', $post->title);
        $this->assertEquals('Body in Italiano', $post->body);

    }

    public function test_if_a_key_not_present_in_the_translatable_array_is_updated_an_exception_is_thrown()
    {
        $this->expectException(AttributeNotTranslatableException::class);

        App::setLocale('it');

        $post = Post::create([
            'title' => 'Titolo',
            'body' => 'Body,'
        ]);

        $post->updateOrCreateTranslation(['comments' => 'Io non sono compresa']);

    }

    public function test_the_array_return_the_correct_translations_for_the_translatable_field()
    {
        $post = Post::create([
            'title' => 'Titolo',
            'body' => 'Body,'
        ]);

        $post->translations()->create([
            'lang' => 'it',
            'field' => 'title',
            'text' => 'Titolo in Italiano'
        ]);

        $post->translations()->create([
            'lang' => 'en',
            'text' => 'Title in English',
            'field' => 'title',
        ]);

        $post->translations()->create([
            'lang' => 'it',
            'field' => 'body',
            'text' => 'Body in Italiano'
        ]);

        $post->translations()->create([
            'lang' => 'en',
            'field' => 'body',
            'text' => 'Body in English'
        ]);

        App::setLocale('en');

        $postArray = $post->toArray();

        $this->assertEquals('Title in English', $postArray['title']);
        $this->assertEquals('Body in English', $postArray['body']);

        App::setLocale('it');

        $postArray = $post->toArray();

        $this->assertEquals('Titolo in Italiano', $postArray['title']);
        $this->assertEquals('Body in Italiano', $postArray['body']);
    }

    public function test_it_can_store_multiple_translations()
    {
        $post = Post::create([
            'title' => 'Titolo',
            'body' => 'Body,'
        ]);

        $translations = [
            'title' => [
                'it' => 'Titolo',
                'en' => 'Title'
            ],
            'body' => [
                'it' => 'Body in italiano',
                'en' => 'Body in english'
            ]
        ];

        $post->storeMultipleTranslations($translations);

        $this->assertDatabaseHas('translations', [
            'lang' => 'it',
            'field' => 'title',
            'text' => 'Titolo',
            'translatable_id' => $post->id,
            'translatable_type' => 'TheNonsenseFactory\Translate\Tests\Models\Post'
        ]);

        $this->assertDatabaseHas('translations', [
            'lang' => 'en',
            'field' => 'title',
            'text' => 'Title',
            'translatable_id' => $post->id,
            'translatable_type' => 'TheNonsenseFactory\Translate\Tests\Models\Post'
        ]);

        $this->assertDatabaseHas('translations', [
            'lang' => 'it',
            'field' => 'body',
            'text' => 'Body in italiano',
            'translatable_id' => $post->id,
            'translatable_type' => 'TheNonsenseFactory\Translate\Tests\Models\Post'
        ]);

        $this->assertDatabaseHas('translations', [
            'lang' => 'en',
            'field' => 'body',
            'text' => 'Body in english',
            'translatable_id' => $post->id,
            'translatable_type' => 'TheNonsenseFactory\Translate\Tests\Models\Post'
        ]);
    }

    public function test_it_create_multiple_translation_if_present_they_will_be_updated()
    {
        $post = Post::create([
            'title' => 'Titolo',
            'body' => 'Body,'
        ]);

        $translations = [
            'title' => [
                'it' => 'Titolo',
                'en' => 'Title'
            ],
            'body' => [
                'it' => 'Body in italiano',
                'en' => 'Body in english'
            ]
        ];

        $post->createOrUpdateMultipleTranslations($translations);

        $this->assertDatabaseHas('translations', [
            'lang' => 'it',
            'field' => 'title',
            'text' => 'Titolo',
            'translatable_id' => $post->id,
            'translatable_type' => 'TheNonsenseFactory\Translate\Tests\Models\Post'
        ]);

        $this->assertDatabaseHas('translations', [
            'lang' => 'en',
            'field' => 'title',
            'text' => 'Title',
            'translatable_id' => $post->id,
            'translatable_type' => 'TheNonsenseFactory\Translate\Tests\Models\Post'
        ]);

        $this->assertDatabaseHas('translations', [
            'lang' => 'it',
            'field' => 'body',
            'text' => 'Body in italiano',
            'translatable_id' => $post->id,
            'translatable_type' => 'TheNonsenseFactory\Translate\Tests\Models\Post'
        ]);

        $this->assertDatabaseHas('translations', [
            'lang' => 'en',
            'field' => 'body',
            'text' => 'Body in english',
            'translatable_id' => $post->id,
            'translatable_type' => 'TheNonsenseFactory\Translate\Tests\Models\Post'
        ]);

        $editedTranslations = [
            'title' => [
                'it' => 'Titolo Edit',
                'en' => 'Title Edit'
            ],
            'body' => [
                'it' => 'Body in italiano edit',
                'en' => 'Body in english edit'
            ]
        ];

        $post->createOrUpdateMultipleTranslations($editedTranslations);

        $this->assertDatabaseHas('translations', [
            'lang' => 'it',
            'field' => 'title',
            'text' => 'Titolo Edit',
            'translatable_id' => $post->id,
            'translatable_type' => 'TheNonsenseFactory\Translate\Tests\Models\Post'
        ]);

        $this->assertDatabaseMissing('translations', [
            'lang' => 'it',
            'field' => 'title',
            'text' => 'Titolo',
            'translatable_id' => $post->id,
            'translatable_type' => 'TheNonsenseFactory\Translate\Tests\Models\Post'
        ]);

        $this->assertDatabaseHas('translations', [
            'lang' => 'en',
            'field' => 'title',
            'text' => 'Title Edit',
            'translatable_id' => $post->id,
            'translatable_type' => 'TheNonsenseFactory\Translate\Tests\Models\Post'
        ]);

        $this->assertDatabaseHas('translations', [
            'lang' => 'it',
            'field' => 'body',
            'text' => 'Body in italiano edit',
            'translatable_id' => $post->id,
            'translatable_type' => 'TheNonsenseFactory\Translate\Tests\Models\Post'
        ]);

        $this->assertDatabaseHas('translations', [
            'lang' => 'en',
            'field' => 'body',
            'text' => 'Body in english edit',
            'translatable_id' => $post->id,
            'translatable_type' => 'TheNonsenseFactory\Translate\Tests\Models\Post'
        ]);

    }

    public function test_it_check_if_a_specific_translation_exists()
    {
        $post = Post::create([
            'title' => 'Titolo',
            'body' => 'Body,'
        ]);

        $post->translations()->create([
            'lang' => 'it',
            'field' => 'title',
            'text' => 'Titolo in Italiano'
        ]);

        $post->translations()->create([
            'lang' => 'en',
            'text' => 'Title in English',
            'field' => 'title',
        ]);

        $needle = [
            'lang' => 'en',
            'field' => 'title',
        ];

        $this->assertTrue($post->checkTranslation($needle));

        $needle = [
            'lang' => 'es',
            'field' => 'title',
        ];

        $this->assertFalse($post->checkTranslation($needle));


    }

    public function test_eager_loaded_translations_do_not_trigger_extra_queries()
    {
        foreach (['Primo', 'Secondo', 'Terzo'] as $title) {
            $post = Post::create(['title' => 'Original', 'body' => 'Body']);
            $post->translations()->create(['lang' => 'it', 'field' => 'title', 'text' => $title]);
        }

        App::setLocale('it');

        $posts = Post::with('translations')->get();

        DB::enableQueryLog();

        $titles = $posts->map(fn ($post) => $post->title)->all();
        $bodies = $posts->map(fn ($post) => $post->body)->all();

        $this->assertSame(['Primo', 'Secondo', 'Terzo'], $titles);
        $this->assertSame(['Body', 'Body', 'Body'], $bodies);
        $this->assertCount(0, DB::getQueryLog());
    }

    public function test_writes_through_the_trait_refresh_an_already_loaded_relation()
    {
        $post = Post::create(['title' => 'Titolo', 'body' => 'Body']);

        App::setLocale('it');

        $post->load('translations');

        $post->updateOrCreateTranslation(['title' => 'Titolo in Italiano']);
        $this->assertEquals('Titolo in Italiano', $post->title);

        $post->load('translations');

        $post->storeMultipleTranslations(['title' => ['it' => 'Titolo Aggiornato']]);
        $this->assertEquals('Titolo Aggiornato', $post->title);
        $this->assertEquals('Titolo Aggiornato', $post->toArray()['title']);
    }

    public function test_a_model_without_the_translatable_property_keeps_working()
    {
        $note = Note::create(['title' => 'Nota']);

        $this->assertEquals('Nota', $note->title);
        $this->assertEquals('Nota', $note->toArray()['title']);
    }

    public function test_update_or_create_translation_handles_multiple_fields()
    {
        $post = Post::create(['title' => 'Titolo', 'body' => 'Body']);

        App::setLocale('it');

        $post->updateOrCreateTranslation(['title' => 'Titolo IT', 'body' => 'Body IT']);

        $this->assertEquals('Titolo IT', $post->title);
        $this->assertEquals('Body IT', $post->body);

        $post->updateOrCreateTranslation(['title' => 'Titolo IT 2', 'body' => 'Body IT 2']);

        $this->assertEquals('Titolo IT 2', $post->title);
        $this->assertEquals('Body IT 2', $post->body);
        $this->assertCount(2, $post->translations()->get());
    }

    public function test_update_or_create_translation_keeps_its_return_values()
    {
        $post = Post::create(['title' => 'Titolo', 'body' => 'Body']);

        App::setLocale('it');

        $created = $post->updateOrCreateTranslation(['title' => 'Titolo IT']);
        $this->assertInstanceOf(Translation::class, $created);

        $updated = $post->updateOrCreateTranslation(['title' => 'Titolo IT 2']);
        $this->assertTrue($updated);
    }

    public function test_a_translation_belongs_to_its_translatable_model()
    {
        $post = Post::create(['title' => 'Titolo', 'body' => 'Body']);

        $translation = $post->translations()->create(['lang' => 'it', 'field' => 'title', 'text' => 'Titolo IT']);

        $this->assertTrue($translation->fresh()->translatable->is($post));
    }

    public function test_store_multiple_translations_validates_every_field_before_writing()
    {
        $post = Post::create(['title' => 'Titolo', 'body' => 'Body']);

        try {
            $post->storeMultipleTranslations([
                'title' => ['it' => 'Titolo IT'],
                'comments' => ['it' => 'Non traducibile'],
            ]);
            $this->fail('AttributeNotTranslatableException was not thrown');
        } catch (AttributeNotTranslatableException $e) {
            $this->assertDatabaseCount('translations', 0);
        }
    }
}
