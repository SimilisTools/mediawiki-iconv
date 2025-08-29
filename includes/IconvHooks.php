<?php

namespace MediaWiki\Extension\Iconv;

use Parser;
use PPFrame;

class IconvHooks
{
    private const ASCII_MAPPING = [
        "ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ«»",
        'SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy""',
    ];
    private const ASCII_REMOVE = [
        "¿", "?", "¡", "—", "·"
    ];

    public static function onParserFirstCallInit(Parser $parser)
    {
        $parser->setFunctionHook('iconv', [ self::class, 'executeIconv' ], Parser::SFH_OBJECT_ARGS);
        $parser->setFunctionHook('toASCII', [ self::class, 'executeToASCII' ], Parser::SFH_OBJECT_ARGS);
        return true;
    }

    public static function executeIconv(Parser $parser, PPFrame $frame, array $args)
    {
        $str = trim($frame->expand($args[0] ?? ''));
        $start = isset($args[1]) ? trim($frame->expand($args[1])) : "UTF-8";
        $end = isset($args[2]) ? trim($frame->expand($args[2])) : "ASCII//TRANSLIT";
        $output = iconv($start, $end, $str);
        return $output;
    }

    public static function executeToASCII(Parser $parser, PPFrame $frame, array $args)
    {
        $str = trim($frame->expand($args[0] ?? ''));
        foreach (self::ASCII_REMOVE as $rm) {
            $str = str_replace($rm, "", $str);
        }
        return strtr(
            utf8_decode($str),
            utf8_decode(self::ASCII_MAPPING[0]),
            self::ASCII_MAPPING[1]
        );
    }
}
