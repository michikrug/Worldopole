<?php

namespace Worldopole;

final class QueryManagerMysqlGolbat extends QueryManagerMysql
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    ///////////
    // Tester
    ///////////

    public function testTotalPokemon()
    {
        $req = 'SELECT COUNT(*) as total FROM pokemon';
        $result = $this->mysqli->query($req);
        if (!is_object($result)) {
            return 1;
        } else {
            $data = $result->fetch_object();
            $total = $data->total;

            if (0 == $total) {
                return 2;
            }
        }

        return 0;
    }

    public function testTotalGyms()
    {
        $req = 'SELECT COUNT(*) as total FROM gym';
        $result = $this->mysqli->query($req);
        if (!is_object($result)) {
            return 1;
        } else {
            $data = $result->fetch_object();
            $total = $data->total;

            if (0 == $total) {
                return 2;
            }
        }

        return 0;
    }

    public function testTotalPokestops()
    {
        $req = 'SELECT COUNT(*) as total FROM pokestop';
        $result = $this->mysqli->query($req);
        if (!is_object($result)) {
            return 1;
        } else {
            $data = $result->fetch_object();
            $total = $data->total;

            if (0 == $total) {
                return 2;
            }
        }

        return 0;
    }

    /////////////
    // Homepage
    /////////////

    public function getTotalPokemon()
    {
        $req = 'SELECT COUNT(*) AS total FROM pokemon WHERE expire_timestamp >= UNIX_TIMESTAMP()';
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getTotalLures()
    {
        $req = 'SELECT COUNT(*) AS total FROM pokestop WHERE lure_expire_timestamp >= UNIX_TIMESTAMP()';
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getTotalGyms()
    {
        $req = 'SELECT COUNT(*) AS total FROM gym';
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getTotalRaids()
    {
        $req = 'SELECT COUNT(*) AS total FROM gym WHERE raid_battle_timestamp <= UNIX_TIMESTAMP() AND raid_end_timestamp >= UNIX_TIMESTAMP()';
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getTotalGymsForTeam($team_id)
    {
        $req = "SELECT COUNT(*) AS total FROM gym WHERE team_id = '".$team_id."'";
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getRecentAll()
    {
        $req = "SELECT pokemon_id, id AS encounter_id,
                expire_timestamp AS disappear_time, changed AS last_modified,
                DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(expire_timestamp), 'UTC', 'Europe/Berlin'), '%Y-%m-%d %H:%i:%s') AS disappear_time_real,
                lat AS latitude, lon AS longitude, cp,
                atk_iv AS individual_attack, def_iv AS individual_defense, sta_iv AS individual_stamina,
                move_1, move_2
                FROM pokemon
                ORDER BY expire_timestamp DESC
                LIMIT 0,12";
        $result = $this->mysqli->query($req);
        $data = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_object()) {
                $data[] = $row;
            }
        }

        return $data;
    }

    public function getRecentMythic($mythic_pokemons)
    {
        $req = "SELECT pokemon_id, id AS encounter_id,
                expire_timestamp AS disappear_time, changed AS last_modified,
                DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(expire_timestamp), 'UTC', 'Europe/Berlin'), '%Y-%m-%d %H:%i:%s') AS disappear_time_real,
                lat AS latitude, lon AS longitude, cp,
                atk_iv AS individual_attack, def_iv AS individual_defense, sta_iv AS individual_stamina,
                move_1, move_2
                FROM pokemon
                WHERE pokemon_id IN (".implode(',', $mythic_pokemons).")
                ORDER BY expire_timestamp DESC
                LIMIT 0,12";
        $result = $this->mysqli->query($req);
        $data = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_object()) {
                $data[] = $row;
            }
        }

        return $data;
    }

    ///////////////////
    // Single Pokemon
    ///////////////////

    public function getGymsProtectedByPokemon($pokemon_id)
    {
        $req = "SELECT COUNT(*) AS total FROM gym WHERE guarding_pokemon_id = '".$pokemon_id."'";
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getPokemonLastSeen($pokemon_id)
    {
        $req = "SELECT expire_timestamp,
                DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(expire_timestamp), 'UTC', 'Europe/Berlin'), '%Y-%m-%d %H:%i:%s') AS disappear_time_real,
                lat AS latitude, lon AS longitude
                FROM pokemon
                WHERE pokemon_id = '".$pokemon_id."'
                ORDER BY expire_timestamp DESC
                LIMIT 0,1";
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getTop50Pokemon($pokemon_id, $top_order_by, $top_direction)
    {
        return array();
        /*
        // Query too expensive
        $req = "SELECT CONVERT_TZ(disappear_time, '+00:00', '".self::$time_offset."') AS distime,
                pokemon_id, disappear_time, latitude, longitude,
                cp, individual_attack, individual_defense, individual_stamina,
                ROUND(100*(individual_attack+individual_defense+individual_stamina)/45,1) AS IV,
                move_1, move_2, form
                FROM pokemon
                WHERE pokemon_id = '".$pokemon_id."' AND move_1 IS NOT NULL AND move_1 <> '0'
                ORDER BY $top_order_by $top_direction, disappear_time DESC
                LIMIT 0,50";
        $result = $this->mysqli->query($req);
        $top = array();
        while ($data = $result->fetch_object()) {
            $top[] = $data;
        }

        return $top;
        */
    }

    public function getTop50Trainers($pokemon_id, $best_order_by, $best_direction)
    {
        $toptrainer = array();

        return $toptrainer;
    }

    public function getPokemonHeatmap($pokemon_id, $start, $end)
    {
        $start = strtotime($start);
        $end = strtotime($end);
        $req = "SELECT lat AS latitude, lon AS longitude
                FROM pokemon
                WHERE pokemon_id = ".$pokemon_id." AND expire_timestamp BETWEEN '".$start."' AND '".$end."'
                LIMIT 10000";
        $result = $this->mysqli->query($req);
        $points = array();
        while ($data = $result->fetch_object()) {
            $points[] = $data;
        }

        return $points;
    }

    public function getPokemonGraph($pokemon_id)
    {
        $req = "SELECT COUNT(*) AS total,
                SELECT DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(expire_timestamp), 'UTC', 'Europe/Berlin'), '%H') AS disappear_hour
                FROM (SELECT expire_timestamp FROM pokemon WHERE pokemon_id = '".$pokemon_id."' LIMIT 100000) AS pokemonFiltered
                GROUP BY disappear_hour
                ORDER BY disappear_hour";
        $result = $this->mysqli->query($req);
        $array = array_fill(0, 24, 0);
        while ($result && $data = $result->fetch_object()) {
            $array[$data->disappear_hour] = $data->total;
        }
        // shift array because AM/PM starts at 1AM not 0:00
        $array[] = $array[0];
        array_shift($array);

        return $array;
    }

    public function getPokemonLive($pokemon_id, $ivMin, $ivMax, $inmap_pokemons)
    {
        $inmap_pkms_filter = '';
        $where = ' WHERE expire_timestamp >= UNIX_TIMESTAMP() AND pokemon_id = '.$pokemon_id;
        if (!is_null($inmap_pokemons) && ('' != $inmap_pokemons)) {
            foreach ($inmap_pokemons as $inmap) {
                $inmap_pkms_filter .= "'".$inmap."',";
            }
            $inmap_pkms_filter = rtrim($inmap_pkms_filter, ',');
            $where .= ' AND id NOT IN ('.$inmap_pkms_filter.') ';
        }
        if (!is_null($ivMin) && ('' != $ivMin)) {
            $where .= ' AND iv >= ('.$ivMin.') ';
        }
        if (!is_null($ivMax) && ('' != $ivMax)) {
            $where .= ' AND iv <= ('.$ivMax.') ';
        }
        $req = "SELECT pokemon_id, id AS encounter_id,
                expire_timestamp AS disappear_time, changed AS last_modified,
                DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(expire_timestamp), 'UTC', 'Europe/Berlin'), '%Y-%m-%d %H:%i:%s') AS disappear_time_real,
                lat AS latitude, lon AS longitude, cp,
                atk_iv AS individual_attack, def_iv AS individual_defense, sta_iv AS individual_stamina,
                move_1, move_2
                FROM pokemon ".$where.'
                LIMIT 5000';
        $result = $this->mysqli->query($req);
        $spawns = array();
        while ($data = $result->fetch_object()) {
            $spawns[] = $data;
        }

        return $spawns;
    }

    public function getPokemonSliderMinMax()
    {
        $req = "SELECT DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(MIN(expire_timestamp)), 'UTC', 'Europe/Berlin'), '%Y-%m-%d %H:%i:%s') AS min,
                DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(MAX(expire_timestamp)), 'UTC', 'Europe/Berlin'), '%Y-%m-%d %H:%i:%s') AS max
                FROM pokemon";
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getMapsCoords()
    {
        $req = 'SELECT MAX(lat) AS max_latitude, MIN(lat) AS min_latitude,
                MAX(lon) AS max_longitude, MIN(lon) as min_longitude
                FROM spawnpoint';
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getPokemonCount($pokemon_id)
    {
        $req = 'SELECT count, last_seen, latitude, longitude
                FROM pokemon_stats_w
                WHERE pid = '.$pokemon_id;
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getPokemonCountAll()
    {
        $req = 'SELECT pid as pokemon_id, count, last_seen, latitude, longitude
                FROM pokemon_stats_w
                GROUP BY pid';
        $result = $this->mysqli->query($req);
        $array = array();
        while ($data = $result->fetch_object()) {
            $array[] = $data;
        }

        return $array;
    }

    public function getRaidCount($pokemon_id)
    {
        /*
        $req = 'SELECT count, last_seen, latitude, longitude
                FROM raid_stats
                WHERE pid = '.$pokemon_id;
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();
        */
        $data = null;

        return $data;
    }

    public function getRaidCountAll()
    {
        /*
        $req = 'SELECT pid as pokemon_id, count, last_seen, latitude, longitude
                FROM raid_stats
                GROUP BY pid';
        $result = $this->mysqli->query($req);
        $array = array();
        while ($data = $result->fetch_object()) {
            $array[] = $data;
        }
        */
        $array = array();
    
        return $array;
    }

    ///////////////
    // Pokestops
    //////////////

    public function getTotalPokestops()
    {
        $req = 'SELECT COUNT(*) as total FROM pokestop';
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getAllPokestops($only_lured = false)
    {
        $req = "SELECT lat AS latitude, lon AS longitude, lure_expire_timestamp AS lure_expiration, UNIX_TIMESTAMP() AS now,
                DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(lure_expire_timestamp), 'UTC', 'Europe/Berlin'), '%Y-%m-%d %H:%i:%s') AS lure_expiration_real
                FROM pokestop";
        if ($only_lured) {
            $req .= ' WHERE lure_expire_timestamp > UNIX_TIMESTAMP()';
        }
        $result = $this->mysqli->query($req);
        $pokestops = array();
        while ($data = $result->fetch_object()) {
            $pokestops[] = $data;
        }

        return $pokestops;
    }

    /////////
    // Gyms
    /////////

    public function getTeamGuardians($team_id)
    {
        $req = "SELECT COUNT(*) AS total, guarding_pokemon_id AS guard_pokemon_id
                FROM gym WHERE team_id = '".$team_id."'
                GROUP BY guarding_pokemon_id
                ORDER BY total DESC
                LIMIT 0,3";
        $result = $this->mysqli->query($req);
        $datas = array();
        while ($data = $result->fetch_object()) {
            $datas[] = $data;
        }

        return $datas;
    }

    public function getOwnedAndPoints($team_id)
    {
        $req = "SELECT COUNT(*) AS total,
                ROUND(AVG(total_cp),0) AS average_points
                FROM gym
                WHERE team_id = '".$team_id."'";
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getAllGyms()
    {
        $req = "SELECT id AS gym_id, team_id, lat AS latitude, lon AS longitude,
                DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(updated), 'UTC', 'Europe/Berlin'), '%Y-%m-%d %H:%i:%s') AS last_scanned,
                (6 - available_slots) AS level
                FROM gym";
        $result = $this->mysqli->query($req);
        $gyms = array();
        while ($data = $result->fetch_object()) {
            $gyms[] = $data;
        }

        return $gyms;
    }

    public function getGymData($gym_id)
    {
        $req = "SELECT name, description, url, team_id AS team,
                DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(updated), 'UTC', 'Europe/Berlin'), '%Y-%m-%d %H:%i:%s') AS last_scanned,
                guarding_pokemon_id AS guard_pokemon_id,
                total_cp, (6 - available_slots) AS level
                FROM gym
                WHERE id='".$gym_id."'";
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getGymDefenders($gym_id)
    {
        $defenders = array();

        return $defenders;
    }

    ////////////////
    // Gym History
    ////////////////

    public function getGymHistories($gym_name, $team, $page, $ranking)
    {
        $gym_history = array();

        return $gym_history;
    }

    public function getGymHistoriesPokemon($gym_id)
    {
        $pokemons = array();

        return $pokemons;
    }

    public function getHistoryForGym($page, $gym_id)
    {
        $last_page = true;
        $history = array();

        return array('last_page' => $last_page, 'data' => $history);
    }

    private function getHistoryForGymPokemon($pkm_uids)
    {
        $pokemons = array();

        return $pokemons;
    }

    public function getGymShaver($gym_name, $team, $page, $ranking)
    {
        $shavers = array();

        return $shavers;
    }

    public function getGymShaverCount()
    {
        $stats = new stdClass();
        $stats->total = 0;
        $stats->week = 0;
        $stats->day = 0;
        $shavers = array();
        $victims = array();

        return [$shavers, $victims, $stats];
    }

    ///////////
    // Raids
    ///////////

    public function getAllRaids($level, $page)
    {
        $lvl = "";
        if ($level > 0) {
            $lvl = " AND raid_level = '".$level."'";
        }
        $req = "SELECT id AS gym_id, raid_level AS level,
                raid_pokemon_id AS pokemon_id, raid_pokemon_cp AS cp,
                raid_pokemon_move_1 AS move_1, raid_pokemon_move_2 AS move_2,
                DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(raid_spawn_timestamp), 'UTC', 'Europe/Berlin'), '%Y-%m-%d %H:%i:%s') AS spawn,
                DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(raid_battle_timestamp), 'UTC', 'Europe/Berlin'), '%Y-%m-%d %H:%i:%s') AS start,
                DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(raid_end_timestamp), 'UTC', 'Europe/Berlin'), '%Y-%m-%d %H:%i:%s') AS end,
                DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(updated), 'UTC', 'Europe/Berlin'), '%Y-%m-%d %H:%i:%s') AS last_scanned,
                name, lat AS latitude, lon AS longitude
                FROM gym
                WHERE raid_end_timestamp > UNIX_TIMESTAMP()".$lvl."
                ORDER BY raid_level DESC, raid_end_timestamp
                LIMIT ".($page * 10).",10";
        $result = $this->mysqli->query($req);
        $raids = array();
        while ($data = $result->fetch_object()) {
            $raids[] = $data;
        }

        return $raids;
    }

    //////////////
    // Trainers
    //////////////

    public function getTrainers($trainer_name, $team, $page, $ranking)
    {
        $trainers = array();

        return $trainers;
    }

    public function getTrainerLevelCount($team_id)
    {
        $levelData = array();

        return $levelData;
    }

    private function getTrainerData($trainer_name, $team, $page, $ranking)
    {
        $trainers = array();

        return $trainers;
    }

    private function getTrainerLevelRating($level)
    {
        return null;
    }

    private function getTrainerActivePokemon($trainer_name)
    {
        $pokemons = array();

        return $pokemons;
    }

    private function getTrainerInactivePokemon($trainer_name)
    {
        $pokemons = array();

        return $pokemons;
    }

    /////////
    // Cron
    /////////

    public function getPokemonCountsActive()
    {
        $req = 'SELECT pokemon_id, COUNT(*) as total
                FROM pokemon
                WHERE expire_timestamp >= UNIX_TIMESTAMP()
                GROUP BY pokemon_id';
        $result = $this->mysqli->query($req);
        $counts = array();
        while ($data = $result->fetch_object()) {
            $counts[$data->pokemon_id] = $data->total;
        }

        return $counts;
    }


    public function getTotalPokemonIV()
    {
        $req = 'SELECT COUNT(*) as total
                FROM pokemon
                WHERE expire_timestamp >= UNIX_TIMESTAMP() AND iv IS NOT NULL';
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }

    public function getPokemonCountsLastDay()
    {
        $counts = array();

        return $counts;
    }

    public function getCaptchaCount()
    {
        return null;
    }

    public function getNestData($time, $minLatitude, $maxLatitude, $minLongitude, $maxLongitude)
    {
        $pokemon_exclude_sql = '';
        if (!empty(self::$config->system->nest_exclude_pokemon)) {
            $pokemon_exclude_sql = 'AND pokemon_id NOT IN ('.implode(',', self::$config->system->nest_exclude_pokemon).')';
        }
        $req = 'SELECT spawn_id AS spawnpoint_id, pokemon_id, MAX(lat) AS latitude, MAX(lon) AS longitude, count(pokemon_id) AS total_pokemon,
                MAX(expire_timestamp) as latest_seen
                FROM pokemon
                WHERE expire_timestamp > (UNIX_TIMESTAMP() - '.($time*3600).')
                AND lat >= '.$minLatitude.' AND lat < '.$maxLatitude.' AND lon >= '.$minLongitude.' AND lon < '.$maxLongitude.'
                '.$pokemon_exclude_sql.'
                GROUP BY spawn_id, pokemon_id
                HAVING COUNT(pokemon_id) >= '.($time / 4).'
                ORDER BY pokemon_id';
        $result = $this->mysqli->query($req);
        $nests = array();
        while ($data = $result->fetch_object()) {
            $nests[] = $data;
        }

        return $nests;
    }

    public function getSpawnpointCount($minLatitude, $maxLatitude, $minLongitude, $maxLongitude)
    {
        $req = 'SELECT COUNT(*) as total
                FROM spawnpoint
                WHERE lat >= '.$minLatitude.' AND lat < '.$maxLatitude.' AND lon >= '.$minLongitude.' AND lon < '.$maxLongitude;
        $result = $this->mysqli->query($req);
        $data = $result->fetch_object();

        return $data;
    }
}
