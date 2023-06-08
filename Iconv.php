<?php

if (!defined("MEDIAWIKI")) {
    die(
        "This file is an extension to the MediaWiki software and cannot be used standalone.\n"
    );
}

//self executing anonymous function to prevent global scope assumptions
call_user_func(function () {
    $GLOBALS["wgExtensionCredits"]["parserhook"][] = [
        "path" => __FILE__,
        "name" => "Iconv",
        "description" => "Allows using Iconv and convert to ASCII in MediaWiki",
        "version" => "0.2.1",
        "author" => "@toniher",
        "url" => "https://mediawiki.org/wiki/User:Toniher",
    ];

    // http://stackoverflow.com/questions/3542717/how-to-transliterate-accented-characters-into-plain-ascii-characters

    $GLOBALS["wgExtensionMessagesFiles"]["Iconv"] = __DIR__ . "/Iconv.i18n.php";
    $GLOBALS["wgExtensionMessagesFiles"]["IconvMagic"] =
        __DIR__ . "/Iconv.i18n.magic.php";

    # Define a setup function
    $GLOBALS["wgHooks"]["ParserFirstCallInit"][] = "wfIconv_Setup";

    // Mapping of characters
    $GLOBALS["wgIconv_ASCIImapping"] = [
        "ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ«»",
        'SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy""',
    ];

    // Characters to remove
    $GLOBALS["wgIconv_ASCIIremove"][] = ["¿", "?", "¡", "—", "·"];
});

function wfIconv_Setup(&$parser)
{
    $parser->setFunctionHook("iconv", "executeIconv", SFH_OBJECT_ARGS);
    $parser->setFunctionHook("toASCII", "executeToASCII", SFH_OBJECT_ARGS);
    return true;
}

function executeIconv($parser, $frame, $args)
{
    $str = trim($frame->expand($args[0]));

    $start = "UTF-8";
    $end = "ASCII//TRANSLIT";

    if ($args[1]) {
        $start = trim($frame->expand($args[1]));
    }
    if ($args[2]) {
        $end = trim($frame->expand($args[2]));
    }

    $output = iconv($start, $end, $str);

    return $output;
}

function executeToASCII($parser, $frame, $args)
{
    global $wgIconv_ASCIImapping;
    global $wgIconv_ASCIIremove;

    $str = trim($frame->expand($args[0]));

    # Remove chars
    foreach ($wgIconv_ASCIIremove as $rm) {
        $str = str_replace($rm, "", $str);
    }

    return strtr(
        utf8_decode($str),
        utf8_decode($wgIconv_ASCIImapping[0]),
        $wgIconv_ASCIImapping[1]
    );
}
