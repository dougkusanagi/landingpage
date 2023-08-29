<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;
use Nwidart\Modules\Facades\Module;
use Illuminate\Filesystem\Filesystem;

class CreateTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('category_id');
            $table->string('name', 190);
            $table->string('thumb', 190)->nullable();
            $table->longText('thank_you_page')->nullable();
            $table->longText('content')->nullable();
            $table->longText('style')->nullable();
            $table->boolean('active')->default(false);
            $table->boolean('is_premium')->default(false);
            $table->timestamps();
        });
        $module = Module::find('TemplateLandingPage');
        
        if ($module) {

            $path =  $module->getPath().'/Database/Seeders/sql/template.sql';
            if(File::exists($path)) {
                \DB::unprepared(file_get_contents($path));
            }
            // Move media default template
            $this->copyAssets();
        }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('templates');
    }

    private function copyAssets() {

        $media_folder_path = __DIR__ . '/../../Files/content_media';
        $media_folder_publish_path = public_path('storage/content_media');

        $templates_folder_path = __DIR__ . '/../../Files/thumb_templates';
        $templates_folder_publish_path = public_path('storage/thumb_templates');

        $thumb_block_folder_path = __DIR__ . '/../../Files/thumb_blocks';
        $thumb_block_folder_publish_path = public_path('storage/thumb_blocks');

        $system = new Filesystem();
        
        if($system->exists($media_folder_path)) {
            $system->copyDirectory($media_folder_path, $media_folder_publish_path);
        }
        if($system->exists($templates_folder_path)) {
            $system->copyDirectory($templates_folder_path, $templates_folder_publish_path);
        }

        if($system->exists($thumb_block_folder_path)) {
            $system->copyDirectory($thumb_block_folder_path, $thumb_block_folder_publish_path);
        }
    }
}
