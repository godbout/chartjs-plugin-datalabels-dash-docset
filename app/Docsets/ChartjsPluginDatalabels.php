<?php

namespace App\Docsets;

use Godbout\DashDocsetBuilder\Docsets\BaseDocset;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Wa72\HtmlPageDom\HtmlPageCrawler;

class ChartjsPluginDatalabels extends BaseDocset
{
    public const CODE = 'chartjs-plugin-datalabels';
    public const NAME = 'chartjs-plugin-datalabels';
    public const URL = 'chartjs-plugin-datalabels.netlify.app';
    public const INDEX = 'guide/index.html';
    public const PLAYGROUND = '';
    public const ICON_16 = '../../icons/icon.png';
    public const ICON_32 = '../../icons/icon@2x.png';
    public const EXTERNAL_DOMAINS = [
    ];


    public function entries(string $file): Collection
    {
        $crawler = HtmlPageCrawler::create(Storage::get($file));

        $entries = collect();

        $entries = $entries->merge($this->guideEntries($crawler, $file));
        $entries = $entries->merge($this->sectionEntries($crawler, $file));

        return $entries;
    }

    protected function guideEntries(HtmlPageCrawler $crawler, string $file)
    {
        $entries = collect();

        if (Str::contains($file, "{$this->url()}/guide/index.html")) {
            $crawler
                ->filter('.sidebar-links > li > a')
                ->each(function (HtmlPageCrawler $node) use ($entries) {
                    $entries->push([
                        'name' => $node->text(),
                        'type' => 'Guide',
                        'path' => $this->url() . '/guide/' . $node->attr('href'),
                    ]);
                });
        }

        return $entries;
    }

    protected function sectionEntries(HtmlPageCrawler $crawler, string $file)
    {
        $entries = collect();

        $crawler->filter('h2')->each(function (HtmlPageCrawler $node) use ($entries, $file) {
            $entries->push([
                'name' => $node->text(),
                'type' => 'Section',
                'path' => Str::after($file . '#' . Str::slug($node->text()), $this->innerDirectory())
            ]);
        });

        return $entries;
    }

    public function format(string $file): string
    {
        $crawler = HtmlPageCrawler::create(Storage::get($file));

        $this->removeLeftSidebar($crawler);
        $this->removeHeaderContentExceptSamples($crawler);
        $this->removeEditLink($crawler);

        $this->insertDashTableOfContents($crawler);

        return $crawler->saveHTML();
    }

    protected function removeLeftSidebar(HtmlPageCrawler $crawler)
    {
        $crawler->filter('.sidebar')->remove();
    }

    protected function removeHeaderContentExceptSamples(HtmlPageCrawler $crawler)
    {
        $header = $crawler->filter('header');

        $header->filter('header > *:not(:last-child)')->remove();
        $header->filter('header form')->remove();
        $header->filter('header .nav-item:not(:nth-child(3))')->remove();
        $header->filter('header nav > a')->remove();
    }

    protected function removeEditLink(HtmlPageCrawler $crawler)
    {
        $crawler->filter('.edit-link')->remove();
    }

    protected function insertDashTableOfContents(HtmlPageCrawler $crawler)
    {
        $crawler->filter('head')
            ->before('<a name="//apple_ref/cpp/Section/Top" class="dashAnchor"></a>');

        $crawler->filter('h2, h3')->each(static function (HtmlPageCrawler $node) {
            $node->before(
                '<a id="' . Str::slug($node->text()) . '" name="//apple_ref/cpp/Section/' . rawurlencode($node->text()) . '" class="dashAnchor"></a>'
            );
        });
    }
}
