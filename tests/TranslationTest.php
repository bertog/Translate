<?php

namespace TheNonsenseFactory\Translate\Tests;

use Aitems\Item;
use Aitems\ItemType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use TheNonsenseFactory\Translate\Exceptions\AttributeNotTranslatableException;
use TheNonsenseFactory\Translate\Tests\Models\Post;


class TranslationTest extends TestCase
{

    /** @test */
    public function it_can_save_a_translation_for_a_model()
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

    /** @test */
    public function it_can_get_the_translation_in_a_specific_lang()
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

    /** @test */
    public function item_description_follow_the_App_Localisation()
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

    /** @test */
    public function if_the_description_in_the_specified_language_does_not_exists_provide_the_fallback()
    {
        $title = 'Titolo in Italiano';

        $post = Post::create([
            'title' => $title,
            'body' => 'Body,'
        ]);

        App::setLocale('en');

        $this->assertEquals($title, $post->title);

    }

    /** @test */
    public function it_create_if_not_present_a_translation()
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

    /** @test */
    public function it_update_a_traslation_if_is_present()
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

    /** @test */
    public function if_a_key_not_present_in_the_translatable_array_is_updated_an_exception_is_thrown()
    {
        $this->expectException(AttributeNotTranslatableException::class);

        App::setLocale('it');

        $post = Post::create([
            'title' => 'Titolo',
            'body' => 'Body,'
        ]);

        $post->updateOrCreateTranslation(['comments' => 'Io non sono compresa']);

    }

    /** @test */
    public function the_array_return_the_correct_translations_for_the_translatable_field()
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

    /** @test */
    function it_can_store_multiple_translations()
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

    /** @test */
    function it_create_multiple_translation_if_present_they_will_be_updated()
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

    /** @test */
    function it_check_if_a_specific_translation_exists()
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

}
