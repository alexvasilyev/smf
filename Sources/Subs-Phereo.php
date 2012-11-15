<?php

if (!defined('SMF')) {
    die('Hacking attempt...');
}

/**
 * Custom Phereo image BBC code
 *
 * @param array $codes
 */
function phereo_bbc_code(&$codes)
{
    /*
     * With width and(or) height params
     */
    $codes[] = array(
        'tag' => 'phereo',
        'type' => 'unparsed_content',
        'parameters' => array(
            'width' => array('optional' => true, 'value' => ' width="$1"', 'match' => '(\d{2,4})'),
            'height' => array('optional' => true, 'value' => ' height="$1"', 'match' => '(\d{2,4})'),
        ),
        'content' => '<iframe rel="phereo" src="http://phereo.com/e/embed/$1/anaglyph/"{width}{height} frameborder="0"></iframe>',
        'disabled_content' => '(phereo_image_$1)',
    );

    /**
     * Without params
     */
    $codes[] = array(
        'tag' => 'phereo',
        'type' => 'unparsed_content',
        'content' => '<iframe rel="phereo" src="http://phereo.com/e/embed/$1/anaglyph/" frameborder="0"></iframe>',
        'disabled_content' => '($1)',
    );
}
add_integration_function('integrate_bbc_codes', 'phereo_bbc_code');

/**
 * Custom Phereo image button
 *
 * @global array $txt
 * @param array $bbc_tags
 */
function phereo_bbc_buttons($bbc_tags)
{
    global $txt;

    $bbc_tags[1][] = array(
            'image' => 'phereo',
            'code' => 'phereo',
            'before' => '[phereo width=480 height=240]',
            'after' => '[/phereo]',
            'description' => $txt['phereo_button']
    );
}
add_integration_function('integrate_bbc_buttons', 'phereo_bbc_buttons');
