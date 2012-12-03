<?php

function phereo_upload()
{
    global $modSettings, $settings;

    $result = array(
        'status' => 'Err',
        'msg' => 'unknown error',
    );

    $uploadDir = $settings['theme_dir'] . '/phereo_upload';
    $fileTypes = array('jpg', 'png', 'bmp', 'tiff', 'pns', 'jps', 'mpo');

    if (!empty($_FILES)) {
        $tempFile = $_FILES['Filedata']['tmp_name'];
        $targetFile = $uploadDir . '/' . $_FILES['Filedata']['name'];
        $fileParts = pathinfo($_FILES['Filedata']['name']);

        if (!in_array($fileParts['extension'], $fileTypes)) {
            $result['msg'] = 'Wrong file extension';

            echo json_encode($result);
            exit;
        }

        if (!move_uploaded_file($tempFile, $targetFile)) {
            $result['msg'] = 'Couldn\'t move uploaded file';

            echo json_encode($result);
            exit;
        }

        if ('mpo' == $_POST['phereo_type']) {
            $eye = 'mpo';
        } else {
            $eye = $_POST['sbs_type'];
        }

        $re = phereo_upload_to_server($targetFile, $eye);

        if ('OK' != $re->status) {
            $result['msg'] = 'Couldn\'t upload image to phereo: ' . $re['message'];

            echo json_encode($result);
            exit;
        }

        // remove temp file
        unlink($targetFile);

        $result['status'] = 'Ok';
        $result['msg'] = 'Successfully uploaded image';
        $result['image_id'] = $re->imageId;
    }

    echo json_encode($result);
    exit;
}

function phereo_upload_to_server($filePath, $eye)
{
    global $modSettings;

    $userName = $modSettings['phereo_account_user'];
    $userPass = $modSettings['phereo_account_pass'];
    $userId = phereo_get_user_id($userName);

    $apiUrl = 'http://api.phereo.com/uploadimage';

    $data['Filedata'] = "@{$filePath}";
    $data['eye'] = $eye;
    $data['userId'] = $userId;
    $data['userApi'] = md5($userPass);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'SMF plug-in');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);

    return json_decode($response);
}

/**
 * @param string $userName
 * @return string|boolean
 */
function phereo_get_user_id($userName)
{
    $apiUrl = 'http://api.phereo.com/api/open/userprofile';
    $fieldsString = 'username=' . urlencode($userName);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json'
    ));
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt ($ch, CURLOPT_USERAGENT, 'SMF plug-in');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsString);
    $response = curl_exec($ch);

    $returnData = json_decode($response);

    if (!$returnData->id) {
        return false;
    }

    return $returnData->id;
}
