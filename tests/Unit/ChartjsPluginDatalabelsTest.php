<?php

namespace Tests\Unit;

use App\Docsets\ChartjsPluginDatalabels;
use Godbout\DashDocsetBuilder\Services\DocsetBuilder;
use Tests\TestCase;

class ChartjsPluginDatalabelsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->docset = new ChartjsPluginDatalabels();
        $this->builder = new DocsetBuilder($this->docset);
    }

    /** @test */
    public function it_can_generate_a_table_of_contents()
    {
        $toc = $this->docset->entries(
            $this->docset->downloadedIndex()
        );

        $this->assertNotEmpty($toc);
    }
}
