<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxDolProfileFields');
bx_import ('BxDolFormMedia');

class BxMatchResultFormAdd extends BxDolFormMedia
{
    var $_oMain, $_oDb;

    function BxMatchResultFormAdd ($oMain, $iProfileId, $iEntryId = 0, $iThumb = 0)
    {
		$this->_oMain = $oMain;
        $this->_oDb = $oMain->_oDb;
		$iNum = $this->_oDb->getMatchTeam($aProfiles, $iEntryId, 0, 2, '', 't',1);
		$iNumPractice = $this->_oDb->getMatchTeam($aProfilesP, $iEntryId, 0, 20, '', '0',1);
		
		$i = 0;
		$match_type = $this->_oDb->getMatchDetails($iEntryId);
		$match_result = $this->_oDb->isMatchResultSubmitted($iEntryId);
		$users = $this->_oDb->getSubmittedMatchResultUser($iEntryId);
		if($match_type['match_type'] == '1') {
		$home_side = explode(',', $users['home_team_players']);
		$away_side = explode(',', $users['away_team_players']);
		
		if($match_result>0)	{
			$home_side_value = array_values($home_side);
			$away_side_value = array_values($away_side);
		} else {
			$home_side_value = true;
			$away_side_value = true;
		}
		
		foreach ($aProfiles as $aProfile) {
			
			$team_players[] = $this->_oDb->getTeamPlayers($aPlayersProfiles, $iEntryId, true, 'p',$aProfile['team_id']);
			$side = ($i==0) ? 'home' : 'away';
			foreach ($aPlayersProfiles as $aPlayersProfile) {
				
				$sProfileThumbPlayer[$side][$aPlayersProfile['ID']] = get_member_thumbnail( $aPlayersProfile['ID'], 'none', ! $bExtMode, 'visitor' );
			}
			$i++;
		}
		} else {
			$home_side_practice = explode(',', $users['home_team_players']);
			if($match_result>0)	{
				$home_side_value_parctice = array_values($home_side_practice);
			} else {
				$home_side_value_parctice = true;
			}
			foreach ($aProfilesP as $aProfilep) {
			
				$sProfileThumbPlayer[$aProfilep['ID']] = get_member_thumbnail( $aProfilep['ID'], 'none', ! $bExtMode, 'visitor' );
			
		}
			
		}
		
        $aCustomForm = array(

            'form_attrs' => array(
                'name'     => 'form_matches_result',
                'action'   => '',
                'method'   => 'post',
                'enctype' => 'multipart/form-data',
            ),

            'params' => array (
                'db' => array(
                    'table' => 'bx_match_result',
                    'key' => 'id',
                    'uri' => 'uri',
                    'uri_title' => 'title',
                    'submit_name' => 'submit_form',
                ),
            ),

            'inputs' => array(

                'header_info' => array(
                    'type' => 'block_header',
                    'caption' => _t('_bx_matches_result_form_header_info')
                ),

                'match_played' => array(
                    'type' => 'radio_set',
                    'name' => 'match_played',
                    'caption' => _t('_bx_matches_result_form_caption_match_played'),
					'value' =>  1,
					'values' => array(
                    0 => _t('_bx_matches_form_caption_match_played_no'),
                    1 => _t('_bx_matches_form_caption_match_played_yes')
					),
                    'required' => true,
                    'checker' => array (
                        'func' => 'int',
                        'error' => _t ('_bx_matches_result_form_err_match_played'),
                    ),
                    'db' => array (
                        'pass' => 'XssHtml',
                    ),
                ),
                'home_team_score' => array(
                    'type' => 'text',
                    'name' => 'home_team_score',
                    'caption' => _t('_bx_matches_result_form_caption_home_team_score'),
                    'required' => true,
					'checker' => array (
                        'func' => 'preg',
						'params' => array('/^[0-9][0-9]*$/'),
                        'error' => _t ('_bx_matches_result_form_err_match_home_score'),
                    ),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                ),
				'away_team_score' => array(
                    'type' => 'text',
                    'name' => 'away_team_score',
                    'caption' => _t('_bx_matches_result_form_caption_away_team_score'),
                    'required' => true,
					'checker' => array (
                        'func' => 'preg',
						'params' => array('/^[0-9][0-9]*$/'),
                        'error' => _t ('_bx_matches_result_form_err_match_away_score'),
                    ),
                    'db' => array (
                        'pass' => 'XssHtml',
                    ),
                ),
				'home_team_players' => array(
					'type' => 'checkbox_set',
					'name' => 'home_team_players',
					'caption' => _t('_home_team_players'),
					'value' => $home_side_value,
					'values' => $sProfileThumbPlayer['home'],
					
				),
				'away_team_players' => array(
					'type' => 'checkbox_set',
					'name' => 'away_team_players',
					'caption' => _t('_away_team_players'),
					'value' => $away_side_value,
					'values' => $sProfileThumbPlayer['away'],
					
				),
                'players_list_practice' => array(
					'type' => 'checkbox_set',
					'name' => 'players_list_practice',
					'caption' => _t('_players_list'),
					'value' => $home_side_value_parctice,
					'values' => $sProfileThumbPlayer,
					
				),
                
                'Submit' => array (
                    'type' => 'submit',
                    'name' => 'submit_form',
                    'value' => _t('_Submit'),
                    'colspan' => false,
                ),
            ),
        );
		if($match_type['match_type'] == 0){
		unset ($aCustomForm['inputs']['away_team_score']);
		unset ($aCustomForm['inputs']['home_team_score']);
		unset ($aCustomForm['inputs']['away_team_players']);
		unset ($aCustomForm['inputs']['home_team_players']);
		} else {
			
			unset ($aCustomForm['inputs']['players_list_practice']);
		}
        parent::BxDolFormMedia ($aCustomForm);
    }

}
