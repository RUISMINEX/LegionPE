<?php

/*
 * LegionPE Theta
 *
 * Copyright (C) 2015 PEMapModder and contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PEMapModder
 */

namespace legionpe\theta\classic\query;

use legionpe\theta\query\LoginDataQuery;

class ClassicLoginDataQuery extends LoginDataQuery{
	public function getQuery(){
		$query = parent::getQuery();
		return "SELECT pvp_init,pvp_kills,pvp_deaths,pvp_curstreak,pvp_maxstreak,pvp_kit,battle_kills,battle_deaths,battle_wins,battle_losses," . substr($query, 7);
	}
	public function getExpectedColumns(){
		$r = parent::getExpectedColumns();
		$r["pvp_init"] = self::COL_UNIXTIME;
		$r["pvp_kills"] = self::COL_INT;
		$r["pvp_deaths"] = self::COL_INT;
		$r["pvp_curstreak"] = self::COL_INT;
		$r["pvp_maxstreak"] = self::COL_INT;
		$r["pvp_kit"] = self::COL_INT;
		$r["battle_kills"] = self::COL_INT;
		$r["battle_deaths"] = self::COL_INT;
		$r["battle_wins"] = self::COL_INT;
		$r["battle_losses"] = self::COL_INT;
		return $r;
	}
}
