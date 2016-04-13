<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTwigModuleDb');

/*
 * Match module Data
 */
class BxMatchDb extends BxDolTwigModuleDb
{
    /*
     * Constructor.
     */
    function BxMatchDb(&$oConfig)
    {
        parent::BxDolTwigModuleDb($oConfig);

        $this->_sTableMain = 'main';
        $this->_sTableMediaPrefix = '';
        $this->_sFieldId = 'id';
        $this->_sFieldAuthorId = 'author_id';
        $this->_sFieldUri = 'uri';
        $this->_sFieldTitle = 'title';
        $this->_sFieldDescription = 'desc';
        $this->_sFieldTags = 'tags';
        $this->_sFieldThumb = 'thumb';
        $this->_sFieldStatus = 'status';
        $this->_sFieldFeatured = 'featured';
        $this->_sFieldCreated = 'created';
        $this->_sFieldJoinConfirmation = 'join_confirmation';
        $this->_sFieldFansCount = 'fans_count';
        $this->_sTableFans = 'fans';
        $this->_sTableAdmins = 'admins';
        $this->_sFieldAllowViewTo = 'allow_view_match_to';
    }

    function deleteEntryByIdAndOwner ($iId, $iOwner, $isAdmin)
    {
        if ($iRet = parent::deleteEntryByIdAndOwner ($iId, $iOwner, $isAdmin)) {
            $this->query ("DELETE FROM `" . $this->_sPrefix . "fans` WHERE `id_entry` = $iId");
            $this->query ("DELETE FROM `" . $this->_sPrefix . "admins` WHERE `id_entry` = $iId");
            $this->deleteEntryMediaAll ($iId, 'images');
            $this->deleteEntryMediaAll ($iId, 'videos');
            $this->deleteEntryMediaAll ($iId, 'sounds');
            $this->deleteEntryMediaAll ($iId, 'files');
        }
        return $iRet;
    }
	
	function updatePgPhoto ($id) {
		
		//$imageid = $this->getOne("SELECT ID FROM `bx_photos_main` WHERE ID = {$id}");
		//$this->query("UPDATE {$this->_sPrefix}pg_main SET `thumb`={$imageid}"
						//. "WHERE id = {$id}");
	}
	
	function getPalgroundListByUser ()
    {
        return $this->getAll ("SELECT * FROM `" . $this->_sPrefix . 'pg_'.$this->_sTableMain . "` WHERE `author_id` = '" . $_COOKIE['memberID'] . "' || `author_id` = 1 ORDER BY `{$this->_sFieldCreated}`");
    }
	
	function getPalgroundDetails ($id)
    {
        return $this->getAll ("SELECT * FROM `" . $this->_sPrefix . 'pg_'.$this->_sTableMain . "` WHERE  `id` = '" . $id . "'");
    }
	function getMatchDetails ($id)
    {
        return $this->getAll ("SELECT match_type, join_confirmation FROM `" . $this->_sPrefix .$this->_sTableMain . "` WHERE  `id` = '" . $id . "'");
    }
	
	function getFans(&$aProfiles, $iEntryId, $isConfirmed, $iStart, $iMaxNum, $aFilter = array(), $type='')
    {
        $isConfirmed = $isConfirmed ? 1 : 0;
        $sFilter = '';
        if ($aFilter) {
            $s = implode (' OR `f`.`id_profile` = ', $aFilter);
            $sFilter = ' AND (`f`.`id_profile` = ' . $s . ') ';
        }
        $aProfiles = $this->getAll ("SELECT SQL_CALC_FOUND_ROWS `p`.*,`f`.* FROM `Profiles` AS `p` INNER JOIN `" . $this->_sPrefix . $this->_sTableFans . "` AS `f` ON (`f`.`id_entry` = '$iEntryId' AND `f`.`id_profile` = `p`.`ID` AND `f`.`confirmed` = $isConfirmed AND `f`.`type` = '$type' AND `p`.`Status` = 'Active' $sFilter) ORDER BY `f`.`when` DESC LIMIT $iStart, $iMaxNum");
        return $this->getOne("SELECT FOUND_ROWS()");
    }
	function getTeamPlayers(&$aProfiles, $iEntryId, $isConfirmed, $type, $team_id)
    {
        $isConfirmed = $isConfirmed ? 1 : 0;
        $aProfiles = $this->getAll ("SELECT SQL_CALC_FOUND_ROWS `p`.*,`f`.* FROM `Profiles` AS `p` INNER JOIN `" . $this->_sPrefix . $this->_sTableFans . "` AS `f` ON (`f`.`id_entry` = '$iEntryId' AND `f`.`id_profile` = `p`.`ID` AND `f`.`confirmed` = $isConfirmed AND `f`.`type` = '$type' AND `f`.`team_id` = '$team_id' AND `p`.`Status` = 'Active') ORDER BY `f`.`when` DESC ");
        return $this->getOne("SELECT FOUND_ROWS()");
    }
	
	function getMatchTeam(&$aProfiles, $iEntryId, $iStart, $iMaxNum, $aFilter = array(), $type, $isConfirmed)
    {
        $isConfirmed = $isConfirmed ? 1 : 0;
        $sFilter = '';
        if ($aFilter) {
            $s = implode (' OR `f`.`id_profile` = ', $aFilter);
            $sFilter = ' AND (`f`.`id_profile` = ' . $s . ') ';
        }
        $aProfiles = $this->getAll ("SELECT SQL_CALC_FOUND_ROWS `p`.*,`f`.* FROM `Profiles` AS `p` INNER JOIN `" . $this->_sPrefix . $this->_sTableFans . "` AS `f` ON (`f`.`id_entry` = '$iEntryId' AND `f`.`id_profile` = `p`.`ID` AND `f`.`confirmed` = '$isConfirmed' AND `f`.`type` = '$type' AND `p`.`Status` = 'Active' $sFilter) ORDER BY `f`.`when` DESC LIMIT $iStart, $iMaxNum");
        return $this->getOne("SELECT FOUND_ROWS()");
    }
	
	function getMatchTeamUnconfirmed(&$aProfiles, $iEntryId, $iStart, $iMaxNum, $aFilter = array(), $type)
    {
        $isConfirmed = $isConfirmed ? 1 : 0;
        $sFilter = '';
        if ($aFilter) {
            $s = implode (' OR `f`.`id_profile` = ', $aFilter);
            $sFilter = ' AND (`f`.`id_profile` = ' . $s . ') ';
        }
        $aProfiles = $this->getAll ("SELECT SQL_CALC_FOUND_ROWS `p`.*,`f`.* FROM `Profiles` AS `p` INNER JOIN `" . $this->_sPrefix . $this->_sTableFans . "` AS `f` ON (`f`.`id_entry` = '$iEntryId' AND `f`.`id_profile` = `p`.`ID`  AND `f`.`type` = '$type' AND `p`.`Status` = 'Active' $sFilter) ORDER BY `f`.`when` DESC LIMIT $iStart, $iMaxNum");
        return $this->getOne("SELECT FOUND_ROWS()");
    }
	function getTeamDetails($id) {
		return $this->getAll ("SELECT * FROM `bx_teams_main` WHERE id='".$id."' ");	
	}
	
	function getMatchInvitationCount($matchid) {
		return $this->getOne ("SELECT count(*) as count FROM `bx_matches_fans` WHERE id_entry='".$matchid."' AND `type`='t'");	
	}
}
