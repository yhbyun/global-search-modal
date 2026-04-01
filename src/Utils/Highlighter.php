<?php

namespace CharrafiMed\GlobalSearchModal\Utils;

class Highlighter
{
    public static function make(?string $text, ?string $pattern, ?string $styles = '', ?string $classes = '', bool $splitWords = false)
    {
        if (blank($pattern)) {
            return $text;
        }

        $highlightedPattern = '<span';

        if (! empty($classes)) {
            $highlightedPattern .= ' class="'.$classes.'"';
        }

        if (! empty($styles)) {
            $highlightedPattern .= ' style="'.$styles.'"';
        }

        $highlightedPattern .= '>$0</span>';

        if ($splitWords) {
            $words = array_unique(array_filter(explode(' ', $pattern)));
            $regex = implode('|', array_map(fn ($w) => preg_quote($w, '/'), $words));
        } else {
            $regex = preg_quote($pattern, '/');
        }

        return preg_replace('/('.$regex.')/iu', $highlightedPattern, $text);
    }
}
