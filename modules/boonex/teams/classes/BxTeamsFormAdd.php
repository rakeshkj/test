<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxDolProfileFields');
bx_import ('BxDolFormMedia');

class BxTeamsFormAdd extends BxDolFormMedia
{
    var $_oMain, $_oDb;

    function BxTeamsFormAdd ($oMain, $iProfileId, $iEntryId = 0, $iThumb = 0)
    {
        $this->_oMain = $oMain;
        $this->_oDb = $oMain->_oDb;

        $this->_aMedia = array ();
        if (BxDolRequest::serviceExists('photos', 'perform_photo_upload', 'Uploader'))
            $this->_aMedia['images'] = array (
                'post' => 'ready_images',
                'upload_func' => 'uploadPhotos',
                'tag' => BX_TEAMS_PHOTOS_TAG,
                'cat' => BX_TEAMS_PHOTOS_CAT,
                'thumb' => 'thumb',
                'module' => 'photos',
                'title_upload_post' => 'images_titles',
                'title_upload' => _t('_bx_teams_form_caption_file_title'),
                'service_method' => 'get_photo_array',
            );

        if (BxDolRequest::serviceExists('videos', 'perform_video_upload', 'Uploader'))
            $this->_aMedia['videos'] = array (
                'post' => 'ready_videos',
                'upload_func' => 'uploadVideos',
                'tag' => BX_TEAMS_VIDEOS_TAG,
                'cat' => BX_TEAMS_VIDEOS_CAT,
                'thumb' => false,
                'module' => 'videos',
                'title_upload_post' => 'videos_titles',
                'title_upload' => _t('_bx_teams_form_caption_file_title'),
                'service_method' => 'get_video_array',
            );

        if (BxDolRequest::serviceExists('sounds', 'perform_music_upload', 'Uploader'))
            $this->_aMedia['sounds'] = array (
                'post' => 'ready_sounds',
                'upload_func' => 'uploadSounds',
                'tag' => BX_TEAMS_SOUNDS_TAG,
                'cat' => BX_TEAMS_SOUNDS_CAT,
                'thumb' => false,
                'module' => 'sounds',
                'title_upload_post' => 'sounds_titles',
                'title_upload' => _t('_bx_teams_form_caption_file_title'),
                'service_method' => 'get_music_array',
            );

        if (BxDolRequest::serviceExists('files', 'perform_file_upload', 'Uploader'))
            $this->_aMedia['files'] = array (
                'post' => 'ready_files',
                'upload_func' => 'uploadFiles',
                'tag' => BX_TEAMS_FILES_TAG,
                'cat' => BX_TEAMS_FILES_CAT,
                'thumb' => false,
                'module' => 'files',
                'title_upload_post' => 'files_titles',
                'title_upload' => _t('_bx_teams_form_caption_file_title'),
                'service_method' => 'get_file_array',
            );

        bx_import('BxDolCategories');
        $oCategories = new BxDolCategories();

        $oProfileFields = new BxDolProfileFields(0);
        $aCountries = $oProfileFields->convertValues4Input('#!Country');
        asort($aCountries);

        // generate templates for custom form's elements
        $aCustomMediaTemplates = $this->generateCustomMediaTemplates ($oMain->_iProfileId, $iEntryId, $iThumb);

        // privacy

        $aInputPrivacyCustom = array ();
        $aInputPrivacyCustom[] = array ('key' => '', 'value' => '----');
        $aInputPrivacyCustom[] = array ('key' => 'f', 'value' => _t('_bx_teams_privacy_fans_only'));
        $aInputPrivacyCustomPass = array (
            'pass' => 'Preg',
            'params' => array('/^([0-9f]+)$/'),
        );

        $aInputPrivacyCustom2 = array (
            array('key' => 'f', 'value' => _t('_bx_teams_privacy_fans')),
            array('key' => 'a', 'value' => _t('_bx_teams_privacy_admins_only'))
        );
        $aInputPrivacyCustom2Pass = array (
            'pass' => 'Preg',
            'params' => array('/^([fa]+)$/'),
        );

        $aInputPrivacyViewFans = $this->_oMain->_oPrivacy->getGroupChooser($iProfileId, 'teams', 'view_fans');
        $aInputPrivacyViewFans['values'] = array_merge($aInputPrivacyViewFans['values'], $aInputPrivacyCustom);

        $aInputPrivacyComment = $this->_oMain->_oPrivacy->getGroupChooser($iProfileId, 'teams', 'comment');
        $aInputPrivacyComment['values'] = array_merge($aInputPrivacyComment['values'], $aInputPrivacyCustom);
        $aInputPrivacyComment['db'] = $aInputPrivacyCustomPass;

        $aInputPrivacyRate = $this->_oMain->_oPrivacy->getGroupChooser($iProfileId, 'teams', 'rate');
        $aInputPrivacyRate['values'] = array_merge($aInputPrivacyRate['values'], $aInputPrivacyCustom);
        $aInputPrivacyRate['db'] = $aInputPrivacyCustomPass;

        $aInputPrivacyForum = $this->_oMain->_oPrivacy->getGroupChooser($iProfileId, 'teams', 'post_in_forum');
        $aInputPrivacyForum['values'] = array_merge($aInputPrivacyForum['values'], $aInputPrivacyCustom);
        $aInputPrivacyForum['db'] = $aInputPrivacyCustomPass;

        $aInputPrivacyUploadPhotos = $this->_oMain->_oPrivacy->getGroupChooser($iProfileId, 'teams', 'upload_photos');
        $aInputPrivacyUploadPhotos['values'] = $aInputPrivacyCustom2;
        $aInputPrivacyUploadPhotos['db'] = $aInputPrivacyCustom2Pass;

        $aInputPrivacyUploadVideos = $this->_oMain->_oPrivacy->getGroupChooser($iProfileId, 'teams', 'upload_videos');
        $aInputPrivacyUploadVideos['values'] = $aInputPrivacyCustom2;
        $aInputPrivacyUploadVideos['db'] = $aInputPrivacyCustom2Pass;

        $aInputPrivacyUploadSounds = $this->_oMain->_oPrivacy->getGroupChooser($iProfileId, 'teams', 'upload_sounds');
        $aInputPrivacyUploadSounds['values'] = $aInputPrivacyCustom2;
        $aInputPrivacyUploadSounds['db'] = $aInputPrivacyCustom2Pass;

        $aInputPrivacyUploadFiles = $this->_oMain->_oPrivacy->getGroupChooser($iProfileId, 'teams', 'upload_files');
        $aInputPrivacyUploadFiles['values'] = $aInputPrivacyCustom2;
        $aInputPrivacyUploadFiles['db'] = $aInputPrivacyCustom2Pass;

        $aCustomForm = array(

            'form_attrs' => array(
                'name'     => 'form_teams',
                'action'   => '',
                'method'   => 'post',
                'enctype' => 'multipart/form-data',
            ),

            'params' => array (
                'db' => array(
                    'table' => 'bx_teams_main',
                    'key' => 'id',
                    'uri' => 'uri',
                    'uri_title' => 'title',
                    'submit_name' => 'submit_form',
                ),
            ),

            'inputs' => array(

                'header_info' => array(
                    'type' => 'block_header',
                    'caption' => _t('_bx_teams_form_header_info')
                ),

                'title' => array(
                    'type' => 'text',
                    'name' => 'title',
                    'caption' => _t('_bx_teams_form_caption_title'),
                    'required' => true,
                    'checker' => array (
                        'func' => 'length',
                        'params' => array(3,100),
                        'error' => _t ('_bx_teams_form_err_title'),
                    ),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                ),
                'desc' => array(
                    'type' => 'textarea',
                    'name' => 'desc',
                    'caption' => _t('_bx_teams_form_caption_desc'),
                    'required' => false,
                    'html' => 2,
                    /*'checker' => array (
                        'func' => 'length',
                        'params' => array(20,64000),
                        'error' => _t ('_bx_teams_form_err_desc'),
                    ),*/
                    'db' => array (
                        'pass' => 'XssHtml',
                    ),
                ),
                'country' => array(
                    'type' => 'hidden',
                    'name' => 'country',
                    'caption' => _t('_bx_teams_form_caption_country'),
                    'value' => 'US',
                    'required' => false,
                    'db' => array (
                        'pass' => 'Preg',
                        'params' => array('/([a-zA-Z]{2})/'),
                    ),
                ),
                'city' => array(
                    'type' => 'number',
                    'name' => 'city',
                    'caption' => _t('_bx_teams_form_caption_range'),
					'info' => _t('_bx_teams_form_info_range'),
                    'required' => false,
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                ),
                'zip' => array(
                    'type' => 'textarea',
                    'name' => 'zip',
                    'caption' => _t('_bx_teams_form_caption_address'),
                    'required' => false,
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                    'display' => true,
                ),
				'gender' => array(
                    'type' => 'radio_set',
                    'name' => 'gender',
                    'caption' => _t('_bx_matches_form_caption_gender'),
					'values' => array(
                    0 => _t('_bx_matches_form_caption_male'),
                    1 => _t('_bx_matches_form_caption_female'),
					2 => _t('_bx_matches_form_caption_any')
					),
                    'required' => true,
                    'db' => array (
                        'pass' => 'Int',
                    ),
                ),
                /*'tags' => array(
                    'type' => 'text',
                    'name' => 'tags',
                    'caption' => _t('_Tags'),
                    'info' => _t('_sys_tags_note'),
                    'required' => true,
                    'checker' => array (
                        'func' => 'avail',
                        'error' => _t ('_bx_teams_form_err_tags'),
                    ),
                    'db' => array (
                        'pass' => 'Tags',
                    ),
                ),*/
				'categories' => array (
                    'type' => 'select',
                    'name' => 'categories',
                    'caption' => _t('_bx_teams_menu_categories'),
                    'info' => _t('_bx_teams_form_info_category'),
                    'values' => array(
                        0 => _t('_5-a-side'),
                        1 => _t('_10-a-side'),
                    ),
                    
                    'db' => array (
                        'pass' => 'Int',
                    ),
                ),
                //'categories' => $oCategories->getGroupChooser ('bx_teams', (int)$iProfileId, true),

                // images

                'header_images' => array(
                    'type' => 'block_header',
                    'caption' => _t('_bx_teams_form_header_images'),
                    'collapsable' => true,
                    'collapsed' => false,
                ),
                'thumb' => array(
                    'type' => 'custom',
                    'content' => $aCustomMediaTemplates['images']['thumb_choice'],
                    'name' => 'thumb',
                    'caption' => _t('_bx_teams_form_caption_thumb_choice'),
                    'info' => _t('_bx_teams_form_info_thumb_choice'),
                    'required' => false,
                    'db' => array (
                        'pass' => 'Int',
                    ),
                ),
                'images_choice' => array(
                    'type' => 'custom',
                    'content' => $aCustomMediaTemplates['images']['choice'],
                    'name' => 'images_choice[]',
                    'caption' => _t('_bx_teams_form_caption_images_choice'),
                    'info' => _t('_bx_teams_form_info_images_choice'),
                    'required' => false,
                ),
                'images_upload' => array(
                    'type' => 'custom',
                    'content' => $aCustomMediaTemplates['images']['upload'],
                    'name' => 'images_upload[]',
                    'caption' => _t('_bx_teams_form_caption_images_upload'),
                    'info' => _t('_bx_teams_form_info_images_upload'),
                    'required' => false,
                ),

                // videos

               /* 'header_videos' => array(
                    'type' => 'block_header',
                    'caption' => _t('_bx_teams_form_header_videos'),
                    'collapsable' => true,
                    'collapsed' => false,
                ),
                'videos_choice' => array(
                    'type' => 'custom',
                    'content' => $aCustomMediaTemplates['videos']['choice'],
                    'name' => 'videos_choice[]',
                    'caption' => _t('_bx_teams_form_caption_videos_choice'),
                    'info' => _t('_bx_teams_form_info_videos_choice'),
                    'required' => false,
                ),
                'videos_upload' => array(
                    'type' => 'custom',
                    'content' => $aCustomMediaTemplates['videos']['upload'],
                    'name' => 'videos_upload[]',
                    'caption' => _t('_bx_teams_form_caption_videos_upload'),
                    'info' => _t('_bx_teams_form_info_videos_upload'),
                    'required' => false,
                ),

                // sounds

                'header_sounds' => array(
                    'type' => 'block_header',
                    'caption' => _t('_bx_teams_form_header_sounds'),
                    'collapsable' => true,
                    'collapsed' => false,
                ),
                'sounds_choice' => array(
                    'type' => 'custom',
                    'content' => $aCustomMediaTemplates['sounds']['choice'],
                    'name' => 'sounds_choice[]',
                    'caption' => _t('_bx_teams_form_caption_sounds_choice'),
                    'info' => _t('_bx_teams_form_info_sounds_choice'),
                    'required' => false,
                ),
                'sounds_upload' => array(
                    'type' => 'custom',
                    'content' => $aCustomMediaTemplates['sounds']['upload'],
                    'name' => 'sounds_upload[]',
                    'caption' => _t('_bx_teams_form_caption_sounds_upload'),
                    'info' => _t('_bx_teams_form_info_sounds_upload'),
                    'required' => false,
                ),

                // files

                'header_files' => array(
                    'type' => 'block_header',
                    'caption' => _t('_bx_teams_form_header_files'),
                    'collapsable' => true,
                    'collapsed' => false,
                ),
                'files_choice' => array(
                    'type' => 'custom',
                    'content' => $aCustomMediaTemplates['files']['choice'],
                    'name' => 'files_choice[]',
                    'caption' => _t('_bx_teams_form_caption_files_choice'),
                    'info' => _t('_bx_teams_form_info_files_choice'),
                    'required' => false,
                ),
                'files_upload' => array(
                    'type' => 'custom',
                    'content' => $aCustomMediaTemplates['files']['upload'],
                    'name' => 'files_upload[]',
                    'caption' => _t('_bx_teams_form_caption_files_upload'),
                    'info' => _t('_bx_teams_form_info_files_upload'),
                    'required' => false,
                ),*/

                // privacy

                'header_privacy' => array(
                    'type' => 'block_header',
                    'caption' => _t('_bx_teams_form_header_privacy'),
                ),

                'allow_view_team_to' => $this->_oMain->_oPrivacy->getGroupChooser($iProfileId, 'teams', 'view_team'),

                //'allow_view_fans_to' => $aInputPrivacyViewFans,

                'allow_comment_to' => $aInputPrivacyComment,

                'allow_rate_to' => $aInputPrivacyRate,

                'allow_post_in_forum_to' => $aInputPrivacyForum,

                'allow_join_to' => $this->_oMain->_oPrivacy->getGroupChooser($iProfileId, 'teams', 'join'),

                'join_confirmation' => array (
                    'type' => 'select',
                    'name' => 'join_confirmation',
                    'caption' => _t('_bx_teams_form_caption_join_confirmation'),
                    'info' => _t('_bx_teams_form_info_join_confirmation'),
                    'values' => array(
                        0 => _t('_bx_teams_form_join_confirmation_disabled'),
                        1 => _t('_bx_teams_form_join_confirmation_enabled'),
                    ),
                    'checker' => array (
                        'func' => 'int',
                        'error' => _t ('_bx_teams_form_err_join_confirmation'),
                    ),
                    'db' => array (
                        'pass' => 'Int',
                    ),
                ),

                'allow_upload_photos_to' => $aInputPrivacyUploadPhotos,

                //'allow_upload_videos_to' => $aInputPrivacyUploadVideos,

                //'allow_upload_sounds_to' => $aInputPrivacyUploadSounds,

                //'allow_upload_files_to' => $aInputPrivacyUploadFiles,

                'Submit' => array (
                    'type' => 'submit',
                    'name' => 'submit_form',
                    'value' => _t('_Submit'),
                    'colspan' => false,
                ),
            ),
        );

        if (!$aCustomForm['inputs']['images_choice']['content']) {
            unset ($aCustomForm['inputs']['thumb']);
            unset ($aCustomForm['inputs']['images_choice']);
        }

        if (!$aCustomForm['inputs']['videos_choice']['content'])
            unset ($aCustomForm['inputs']['videos_choice']);

        if (!$aCustomForm['inputs']['sounds_choice']['content'])
            unset ($aCustomForm['inputs']['sounds_choice']);

        if (!$aCustomForm['inputs']['files_choice']['content'])
            unset ($aCustomForm['inputs']['files_choice']);

        if (!isset($this->_aMedia['images'])) {
            unset ($aCustomForm['inputs']['header_images']);
            unset ($aCustomForm['inputs']['thumb']);
            unset ($aCustomForm['inputs']['images_choice']);
            unset ($aCustomForm['inputs']['images_upload']);
            unset ($aCustomForm['inputs']['allow_upload_photos_to']);
        }

        if (!isset($this->_aMedia['videos'])) {
            unset ($aCustomForm['inputs']['header_videos']);
            unset ($aCustomForm['inputs']['videos_choice']);
            unset ($aCustomForm['inputs']['videos_upload']);
            unset ($aCustomForm['inputs']['allow_upload_videos_to']);
        }

        if (!isset($this->_aMedia['sounds'])) {
            unset ($aCustomForm['inputs']['header_sounds']);
            unset ($aCustomForm['inputs']['sounds_choice']);
            unset ($aCustomForm['inputs']['sounds_upload']);
            unset ($aCustomForm['inputs']['allow_upload_sounds_to']);
        }

        if (!isset($this->_aMedia['files'])) {
            unset ($aCustomForm['inputs']['header_files']);
            unset ($aCustomForm['inputs']['files_choice']);
            unset ($aCustomForm['inputs']['files_upload']);
            unset ($aCustomForm['inputs']['allow_upload_files_to']);
        }

        $this->processMembershipChecksForMediaUploads ($aCustomForm['inputs']);

        parent::BxDolFormMedia ($aCustomForm);
    }

}
