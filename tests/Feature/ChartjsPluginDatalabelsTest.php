<?php

namespace Tests\Feature;

use App\Docsets\ChartjsPluginDatalabels;
use Godbout\DashDocsetBuilder\Services\DocsetBuilder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChartjsPluginDatalabelsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->docset = new ChartjsPluginDatalabels();
        $this->builder = new DocsetBuilder($this->docset);

        if (! Storage::exists($this->docset->downloadedDirectory())) {
            fwrite(STDOUT, PHP_EOL . PHP_EOL . "\e[1;33mGrabbing chartjs-plugin-datalabels..." . PHP_EOL);
            Artisan::call('grab chartjs-plugin-datalabels');
        }

        if (! Storage::exists($this->docset->file())) {
            fwrite(STDOUT, PHP_EOL . PHP_EOL . "\e[1;33mPackaging chartjs-plugin-datalabels..." . PHP_EOL);
            Artisan::call('package chartjs-plugin-datalabels');
        }
    }

    /** @test */
    public function it_has_a_table_of_contents()
    {
        Config::set(
            'database.connections.sqlite.database',
            "storage/{$this->docset->databaseFile()}"
        );

        $this->assertNotEquals(0, DB::table('searchIndex')->count());
    }

    /** @test */
    public function the_left_sidebar_gets_removed_from_the_dash_docset_files()
    {
        $leftSidebar = '"sidebar"';

        $this->assertStringContainsString(
            $leftSidebar,
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertStringNotContainsString(
            $leftSidebar,
            Storage::get($this->docset->innerIndex())
        );
    }

    /** @test */
    public function the_edit_link_gets_removed_from_the_dash_docset_files()
    {
        $editLink = 'edit-link';

        $this->assertStringContainsString(
            $editLink,
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertStringNotContainsString(
            $editLink,
            Storage::get($this->docset->innerIndex())
        );
    }
}
