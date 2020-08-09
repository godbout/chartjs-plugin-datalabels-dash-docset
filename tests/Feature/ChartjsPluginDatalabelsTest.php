<?php

namespace Tests\Feature;

use App\Docsets\ChartjsPluginDatalabels;
use Godbout\DashDocsetBuilder\Services\DocsetBuilder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Wa72\HtmlPageDom\HtmlPageCrawler;

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
    public function the_header_content_except_samples_gets_removed_from_the_dash_docset_files()
    {
        $searchForm = 'search-form';

        $this->assertStringContainsString(
            $searchForm,
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertStringNotContainsString(
            $searchForm,
            Storage::get($this->docset->innerIndex())
        );
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
    public function the_content_gets_centered_in_the_dash_docset_files()
    {
        $crawler = HtmlPageCrawler::create(
            Storage::get($this->docset->downloadedIndex())
        );

        $this->assertNull(
            $crawler->filter('.page')->getStyle('padding-left')
        );

        $crawler = HtmlPageCrawler::create(
            Storage::get($this->docset->innerIndex())
        );

        $this->assertStringContainsString(
            '0',
            $crawler->filter('.page')->getStyle('padding-left')
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

    /** @test */
    public function the_package_version_badges_get_removed_from_the_dash_docset_files()
    {
        $npmBadge = 'https://img.shields.io/npm/v';

        $this->assertStringContainsString(
            $npmBadge,
            Storage::get($this->docset->downloadedDirectory() . '/' . $this->docset->url() . '/guide/getting-started.html')
        );

        $this->assertStringNotContainsString(
            $npmBadge,
            Storage::get($this->docset->innerDirectory() . '/' . $this->docset->url() . '/guide/getting-started.html')
        );
    }

    /** @test */
    public function it_inserts_dash_anchors_in_the_doc_files()
    {
        $this->assertStringContainsString(
            'name="//apple_ref/',
            Storage::get($this->docset->innerIndex())
        );
    }
}
