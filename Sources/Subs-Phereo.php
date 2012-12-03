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
 * @global array $modSettings
 * @param array $bbc_tags
 */
function phereo_bbc_buttons($bbc_tags)
{
    global $txt, $modSettings;

    // embed
    $bbc_tags[1][] = array(
            'image' => 'phereo',
            'code' => 'phereo',
            'before' => '[phereo width=480 height=240]',
            'after' => '[/phereo]',
            'description' => $txt['phereo_button']
    );
    // upload
    if ($modSettings['phereo_allow_upload']) {
        $bbc_tags[1][] = array(
                'image' => 'phereo-upload',
                'code' => 'phereo_upload',
                'before' => '[phereo width=480 height=240]',
                'after' => '[/phereo]',
                'description' => $txt['phereo_button']
        );
    }
}
add_integration_function('integrate_bbc_buttons', 'phereo_bbc_buttons');

function phereo_upload_action($actionArray)
{
    $actionArray['phereo_upload'] = array(
        'PhereoUpload.php',
        'phereo_upload',
    );
}
add_integration_function('integrate_actions', 'phereo_upload_action');

function phereo_settings_section($subActions)
{
    $subActions['phereo'] = 'phereo_modify_settings';
}
add_integration_function('integrate_modify_modifications', 'phereo_settings_section');

function phereo_modify_settings($return_config = false)
{
    global $txt, $scripturl, $context, $settings, $sc, $modSettings;

    $config_vars[] = array(
        'check',
        'phereo_allow_upload',
        1,
        'Allow image upload to phereo',
    );
    $config_vars[] = array(
        'text',
        'phereo_account_user',
        20,
        'Phereo account username',
    );
    $config_vars[] = array(
        'text',
        'phereo_account_pass',
        20,
        'Phereo account password',
    );

    if ($return_config)
        return $config_vars;

    $context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=phereo';
    $context['settings_title'] = $txt['mods_cat_modifications_phereo'];

    if (isset($_GET['save'])) {
        checkSession();
        saveDBSettings($config_vars);
        redirectexit('action=admin;area=modsettings;sa=phereo');
    }

    prepareDBSettingContext($config_vars);
}

/**
 * @return string
 */
function phereo_upload_section()
{
    global $modSettings, $settings;
    $themedir = $settings['theme_url'];
    $result = '';
    // Phereo upload section
    $result .= <<<EOS
<style type="text/css">
    @import url("{$themedir}/css/uploadify.css");
</style>
<script src="{$themedir}/scripts/jquery.uploadify.min.js"></script>
<div id="phereo_upload_div" style="display: none; width: 380px; margin: 30px 0; position: relative; padding: 15px;">
    <h2>Upload form</h2><br />
    <form action="" enctype="multipart/form-data">
        <h3>Choose file type:</h3>
        <label><input id="type-mpo" type="radio" name="phereo_type" value="mpo" />MPO</label>&nbsp;&nbsp;
        <label><input id="type-sbs" type="radio" name="phereo_type" value="sbs" />Side-by-side</label><br />
        <div id="sbs-type-cont" style="display: none;">
            <h3>Select eye mode</h3>
            <label><input id="sbs_type_pll" type="radio" name="sbs_type" value="parallel" />Parallel-eye</label>&nbsp;&nbsp;
            <label><input id="sbs_type_crs" type="radio" name="sbs_type" value="crossed" />Crossed-eye</label><br />
        </div>
        <div id="uplodify_btn_cont" style="margin-top: 30px; display: none;">
            <input id="phereo_file_upload" type="file" name="imagefile" value="" /><br />
        </div>
    </form>
</div>
<div id="upload_result" style="display: none; width: 500px; margin: 30px 0">
    <div>Click here to add stereo image to your post:</div>
    <div id="phereo_insert_btn_cont"></a></div>
</div>
<script>
    $(document).ready(function() {
        $('#phereo_file_upload').uploadify({
            method: 'post',
            multi: false,
            height: 30,
            width: 120,
            removeTimeout: 0,
            swf: '{$themedir}/uploadify.swf',
            uploader: '?action=phereo_upload',
            buttonText: 'Select file...',
            fileTypeExts: '*.mpo;',
            onUploadStart: function(file) {
                $('#phereo_file_upload').uploadify('settings', 'formData', {'phereo_type': $('input[name="phereo_type"]:checked').val()});
                if ('sbs' == $('input[name="phereo_type"]:checked').val()) {
                    $('#phereo_file_upload').uploadify('settings', 'formData', {'sbs_type': $('input[name="sbs_type"]:checked').val()});
                }
                $('#phereo_upload_div').append($('<div id="phereo_overlay" style="z-index: 1001; position: absolute; top: 0; left: 0; width: 100%; opacity: 0.7; background: url(\'{$themedir}/images/ajax-loader.gif\') #e3e3e3 center center no-repeat;">').height($('#phereo_upload_div').height()));
            },
            onUploadSuccess: function(file, data, response) {
                data = $.parseJSON(data);
                image_id = data.image_id;

                var link = $('<a href="#">').text(image_id).on('click', function() {
                    oEditorHandle_message.insertPhereoText($(this).text());

                    return false;
                });;

                $('#phereo_insert_btn_cont').append(link).append('<br />');

                resetForm();
                $('#upload_result').css({display: 'block'});
                oEditorHandle_message.insertPhereoText(image_id);
            }
        });
        $('input[name="phereo_type"]').on('change', function() {
            if ('sbs' == $(this).val()) { // sbs mode
                $('#sbs-type-cont').css({display: 'block'});
                $('#uplodify_btn_cont').css({display: 'none'});
            } else { // mpo mode
                if ('block' == $('#sbs-type-cont').css('display')) {
                    $('#sbs-type-cont').css({display: 'none'});
                    $('input[name="sbs_type"]').removeAttr('checked');
                }
                showUploadBtn('*.mpo');
            }
        });
        $('input[name="sbs_type"]').on('change', function() {
            showUploadBtn('*.jpg; *.bmp; *.tiff; *.png; *.pns; *.jps;');
        });

        function showUploadBtn(ext)
        {
            $('#uplodify_btn_cont').css({display: 'block'});
            // change allowed files extensions
            setTimeout(function() {
                $('#phereo_file_upload').uploadify('settings', 'fileTypeExts', ext);
            }, 200);
        }

        function resetForm()
        {
            setTimeout(function() {
                $('input[name="sbs_type"]:checked').removeAttr('checked');
                $('input[name="phereo_type"]:checked').removeAttr('checked');
                $('#uplodify_btn_cont').css({display: 'none'});
                $('#sbs-type-cont').css({display: 'none'});
                $('#phereo_upload_div').css({display: 'none'});
                $('#phereo_overlay').remove();
            }, 500);
        }
    });
</script>
EOS;

    return $result;
}
