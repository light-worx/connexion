<?php

namespace App\Presentation;

use App\Models\Prayer;
use App\Models\Service;
use App\Models\Song;

class SlideBuilder
{
    public function __construct(
        protected int $songLines = 4,
        protected int $prayerChars = 250,
    ) {}

    public function build(Service $service): array
    {
        $service->load(['series', 'setitems' => fn ($q) => $q->orderBy('sort_order')]);
        $setitems = $service->setitems;

        $songs = Song::whereIn('id', $setitems->where('content_type', 'song')->pluck('content_id'))
            ->get()->keyBy('id');
        $prayers = Prayer::whereIn('id', $setitems->where('content_type', 'prayer')->pluck('content_id'))
            ->get()->keyBy('id');

        $items = [];
        foreach ($setitems as $si) {
            $song   = $si->content_type === 'song' ? $songs->get($si->content_id) : null;
            $prayer = $si->content_type === 'prayer' ? $prayers->get($si->content_id) : null;

            if ($song) {
                $title  = $song->title;
                $slides = $this->songSlides($song);
            } elseif ($prayer) {
                $title  = $prayer->title;
                $slides = $this->prayerSlides($prayer);
            } else {
                $title  = $si->title ?: ucfirst((string) $si->content_type);
                $slides = [['kind' => 'title', 'label' => $title, 'lines' => array_filter([$title, $si->subtitle])]];
            }

            foreach ($slides as $n => &$slide) {
                $slide['id'] = "{$si->id}-{$n}";
            }
            unset($slide);

            $items[] = [
                'id'       => $si->id,
                'type'     => $si->content_type ?: 'other',
                'title'    => $title,
                'subtitle' => $si->subtitle,
                'source'   => ['type' => $si->content_type, 'id' => $si->content_id],
                'slides'   => $slides,
            ];
        }

        return [
            'version'      => 1,
            'generated_at' => now()->toIso8601String(),
            'hash'         => sha1(json_encode($items)),
            'service'      => [
                'id'      => $service->id,
                'date'    => substr((string) $service->servicedate, 0, 10),
                'time'    => $service->servicetime,
                'series'  => $service->series?->series,
                'reading' => $service->reading,
            ],
            'items' => $items,
        ];
    }

    // ── Songs ────────────────────────────────────────────────────────────

    protected function songSlides(Song $song): array
    {
        $sections = $this->songSections((string) $song->lyrics);

        $order = preg_split('/\s+/', trim((string) $song->verseorder), -1, PREG_SPLIT_NO_EMPTY);
        $order = array_values(array_filter($order, fn ($k) => isset($sections[$k])));
        if (! $order) {
            $order = array_keys($sections);
        }

        $slides = [];
        foreach ($order as $key) {
            foreach ($this->chunkSection($sections[$key]) as $lines) {
                $slides[] = [
                    'kind' => 'text', 
                    'label' => $this->label($key), 
                    'lines' => $lines,
                    'flow' => 'lines'
                ];
            }
        }

        return $slides ?: [['kind' => 'title', 'label' => $song->title, 'lines' => [$song->title], 'flow' => 'lines']];
    }

    /** Parses "{V1}" section markers; strips "[G]" chords. */
    protected function songSections(string $lyrics): array
    {
        $sections = [];
        $key = null;

        foreach (preg_split('/\R/', $lyrics) as $line) {
            if (preg_match('/^\s*\{([^}]+)\}(.*)$/', $line, $m)) {
                $key = trim($m[1]);
                $sections[$key] ??= [];
                $line = $m[2];           // text after the marker, if any
                if (trim($line) === '') {
                    continue;
                }
            }
            $key ??= 'V1';
            $sections[$key] ??= [];
            $sections[$key][] = trim(preg_replace('/\[[^\]]*\]/', '', $line));
        }

        return $sections;
    }

    /** Blank lines force a split; long blocks are split into balanced chunks. */
    protected function chunkSection(array $lines): array
    {
        $groups = [[]];
        foreach ($lines as $line) {
            if ($line === '') {
                $groups[] = [];
            } else {
                $groups[array_key_last($groups)][] = $line;
            }
        }

        $chunks = [];
        foreach (array_filter($groups) as $group) {
            $n     = count($group);
            $parts = (int) ceil($n / $this->songLines);
            $base  = intdiv($n, $parts);
            $extra = $n % $parts;
            $offset = 0;
            for ($i = 0; $i < $parts; $i++) {
                $len = $base + ($i < $extra ? 1 : 0);
                $chunks[] = array_slice($group, $offset, $len);
                $offset += $len;
            }
        }

        return $chunks;
    }

    protected function label(string $key): string
    {
        $names = ['V' => 'Verse', 'C' => 'Chorus', 'P' => 'Pre-chorus', 'B' => 'Bridge', 'T' => 'Tag'];
        $type  = $names[strtoupper($key[0])] ?? null;

        return $type ? trim($type . ' ' . substr($key, 1)) : $key;
    }

    // ── Prayers ──────────────────────────────────────────────────────────
    protected function prayerSlides(Prayer $prayer): array
    {
        $html = preg_replace(['#</p>#i', '#<br\s*/?>#i'], ["\n\n", "\n"], (string) $prayer->words);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\u{00A0}", ' ', $text);

        $paras = array_values(array_filter(array_map(
            fn ($p) => trim(preg_replace('/[ \t]+/', ' ', $p)),
            preg_split('/\n\s*\n/u', $text)
        )));

        // group paragraphs into slides up to the character limit
        $slides = [];
        $current = [];
        $len = 0;
        foreach ($paras as $p) {
            if ($current && $len + strlen($p) > $this->prayerChars) {
                $slides[] = $current;
                $current = [];
                $len = 0;
            }
            $current[] = $p;
            $len += strlen($p);
        }
        if ($current) {
            $slides[] = $current;
        }

        $out = array_map(fn ($paragraphs) => [
            'kind'  => 'text',
            'flow'  => 'paragraphs',   // each entry is a paragraph, not a line
            'label' => $prayer->title,
            'lines' => $paragraphs,
        ], $slides);

        return $out ?: [['kind' => 'title', 'label' => $prayer->title, 'lines' => [$prayer->title]]];
    }
}