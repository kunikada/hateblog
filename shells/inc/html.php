<?php
require_once 'curl.php';

class HTML {
    public static function getInfo($url) {
        $result = Curl::getBody($url);
        if (!$result) {
            return $result;
        }

        $content_type = Curl::getInfo(CURLINFO_CONTENT_TYPE);
        $head = self::innertext($result, 'head');
        if ($head === false) {
            return '';
        }
        $head = str_replace(array("\r\n", "\r", "\n"), '', $head);
        $title = self::innertext($head, 'title');
        $description = false;
        while (1) {
            $meta = self::outertext($head, 'meta');
            if ($meta === false) {
                break;
            }
            if (strtolower(self::getAttribute($meta, 'http-equiv')) == 'content-type') {
                $content_type = self::getAttribute($meta, 'content');
            }
            if (strtolower(self::getAttribute($meta, 'name')) == 'description') {
                $description = self::getAttribute($meta, 'content');
            }
            $offset = stripos($head, '<meta') + 5;
            $head = substr($head, $offset);
        }
        if (preg_match('/utf.?8/i', $content_type)) {
            $title = self::mb_trim($title);
            $description = self::mb_trim($description);
            return array($title, $description);
        } elseif (preg_match('/euc.?jp/i', $content_type)) {
            $from = 'eucjp-win';
        } elseif (preg_match('/(shift.?jis|sjis)/i', $content_type)) {
            $from = 'sjis-win';
        } elseif (preg_match('/iso-2022-jp/i', $content_type)) {
            $from = 'iso-2022-jp';
        } elseif (preg_match('/iso-8859-1/i', $content_type)) {
            $from = 'iso-8859-1';
        } else {
            $from = 'auto';
        }
        $title = mb_convert_encoding($title, 'UTF-8', $from);
        $description = mb_convert_encoding($description, 'UTF-8', $from);
        $title = self::mb_trim($title);
        $description = self::mb_trim($description);
        return array($title, $description);
    }

    public static function innertext($text, $tag) {
        $result = $text;
        $result = stristr($result, "</$tag>", true);
        $result = stristr($result, "<$tag");
        $result = strstr($result, '>');
        $result = substr($result, 1);
        return $result;
    }

    public static function outertext($text, $tag) {
        $result = $text;
        $result = stristr($result, "<$tag");
        $result = strstr($result, '>', true);
        if (!$result) {
            return false;
        }
        return $result . '>';
    }

    public static function getAttribute($text, $attribute) {
        if (preg_match("/[\s'\"]$attribute\s*=([^\s'\">]+|'[^']+'|\"[^\"]+\")/si", $text, $matches)) {
            $result = preg_replace('/^\s*[\'"](.+)[\'"]\s*$/s', '$1', $matches[1]);
        } else {
            $result = false;
        }
        return $result;
    }

    public static function mb_trim($text) {
        $whitespace = '[\s\0\x0b\p{Zs}\p{Zl}\p{Zp}]';
        $result = preg_replace(sprintf('/(^%s+|%s+$)/u', $whitespace, $whitespace), '', $text);
        return $result;
    }
}
/* End of file curl.php */
