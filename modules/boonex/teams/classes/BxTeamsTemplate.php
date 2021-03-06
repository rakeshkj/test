<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTwigTemplate');

/*
 * teams module View
 */
class BxTeamsTemplate extends BxDolTwigTemplate
{
    var $_iPageIndex = 500;

    /**
     * Constructor
     */
    function BxTeamsTemplate(&$oConfig, &$oDb)
    {
        parent::BxDolTwigTemplate($oConfig, $oDb);
    }

    function unit ($aData, $sTemplateName, &$oVotingView, $isShort = false)
    {
		$team_detail = $this->_oDb->getTeamDetails($aData['id']);
		$join_type =  ($team_detail[0]['join_confirmation'] == 0)?'open_icon':'invite_only';
		
        if (null == $this->_oMain)
            $this->_oMain = BxDolModule::getInstance('BxTeamsModule');

        if (!$this->_oMain->isAllowedView ($aData)) {
            $aVars = array ('extra_css_class' => 'bx_teams_unit');
            return $this->parseHtmlByName('twig_unit_private', $aVars);
        }

        $sImage = '';
		$team_status = '';
        if ($aData['thumb']) {
            $a = array ('ID' => $aData['author_id'], 'Avatar' => $aData['thumb']);
            $aImage = BxDolService::call('photos', 'get_image', array($a, 'thumb'), 'Search');
            $sImage = $aImage['no_image'] ? '' : $aImage['file'];
        }
		
		$member_count = $aData['fans_count'];
		$team_max_capacity = $this->_oDb->getParam('bx_teams_team_max_capacity');
		$team_min_capacity = $this->_oDb->getParam('bx_teams_team_min_capacity');
		if($member_count >= $team_max_capacity) {
			$team_status = 'status_team_max_capacity_reached';
			
		} elseif($member_count >= $team_min_capacity) {
			$team_status = 'status_team_Complete';
			
		} else {
			$team_status = 'status_team_Incomplete';
		}
		$team_status = $this->_oMain->getIconFromText($team_status);
		$player_icon = $this->_oMain->getIconFromText('Player-Icon');
		//Gender
		if($team_detail[0]['gender']==0) {
			$gender = 'Male';
			
		} elseif($team_detail[0]['gender']==1) {
			$gender = 'Female';
		} elseif($team_detail[0]['gender']==2) {
			$gender = '';
		}
		//$gender = $this->_oMain->getIconFromText($gender);
		if($gender!='') {
			$gender = $this->_oMain->getIconFromText($gender);
			$gender = '<img class="team-class" src="'.$gender.'" alt="">';
		}
//echo $this->_oMain->getIconFromText('5aside');
        $aVars = array (
            'id' => $aData['id'],
            'thumb_url' => $sImage ? $sImage : $this->getImageUrl('no-image-thumb.png'),
            'team_url' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aData['uri'],
            'team_title' => $aData['title'],
			'join_type_icon'  => $this->_oMain->getIconFromText($join_type),//$join_type,
			'team_status'  => $team_status,//$join_type,
            'created' => defineTimeInterval($aData['created']),
            'fans_count' => $aData['fans_count'],
			'player_icon' => $player_icon,
			'gender' => $gender,
            'country_city' => $this->_oMain->_formatLocation($aData),
            'snippet_text' => $this->_oMain->_formatSnippetText($aData),
            'bx_if:full' => array (
                'condition' => !$isShort,
                'content' => array (
                    'author' => getNickName($aData['author_id']),
                    'author_url' => $aData['author_id'] ? getProfileLink($aData['author_id']) : 'javascript:void(0);',
                    'created' => defineTimeInterval($aData['created']),
                    'rate' => $oVotingView ? $oVotingView->getJustVotingElement(0, $aData['id'], $aData['rate']) : '&#160;',
                ),
            ),
        );

        return $this->parseHtmlByName($sTemplateName, $aVars);
    }

    // ======================= ppage compose block functions

    function blockDesc (&$aDataEntry)
    {
        $aVars = array (
            'description' => $aDataEntry['desc'],
        );
        return $this->parseHtmlByName('block_description', $aVars);
    }

    function blockFields (&$aDataEntry)
    {
        $sRet = '<table class="bx_teams_fields">';
        bx_teams_import ('FormAdd');
        $oForm = new BxTeamsFormAdd ($GLOBALS['oBxTeamsModule'], getLoggedId());
        foreach ($oForm->aInputs as $k => $a) {
            if (!isset($a['display']) || !$aDataEntry[$k]) continue;
            $sRet .= '<tr><td class="bx_teams_field_name bx-def-font-grayed bx-def-padding-sec-right" valign="top">' . $a['caption'] . '</td><td class="bx_teams_field_value">';
            if (is_string($a['display']) && is_callable(array($this, $a['display'])))
                $sRet .= call_user_func_array(array($this, $a['display']), array($aDataEntry[$k]));
            else if (0 == strcasecmp($k, 'country'))
                $sRet .= _t($GLOBALS['aPreValues']['Country'][$aDataEntry[$k]]['LKey']);
            else
                $sRet .= $aDataEntry[$k];
            $sRet .= '</td></tr>';
        }
        $sRet .= '</table>';
        return $sRet;
    }
}
