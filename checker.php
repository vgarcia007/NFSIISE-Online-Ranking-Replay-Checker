<?php
include_once('modules/nfs2se-ranking/settings.php');

function open_file($file_path){
    $filesize = filesize($file_path);
    $fp = fopen($file_path, 'rb');
    $file = fread($fp, $filesize);
    fclose($fp);
    return $file;
}

/**
 * Extracts the player name from a given file.
 * @param string $file The replay file.
 * @return array An associative array containing player name information:
 *               - 'id': The identifier (always set to 0 for player name).
 *               - 'text': The extracted player name.
 */

function get_player_name($file){
    $data = array();
    $player_name = substr($file, 172, 9);
    $player_name = explode("\0", $player_name)[0];
    $data['id'] = 0;
    $data['text'] = $player_name;
    return $data;
}

/**
 * Extracts information about a player's car from a given file.
 * @param string $file The replay file.
 * @return array An associative array containing car information:
 *               - 'id': The car type ID extracted from the file.
 *               - 'text': The descriptive text for the car type.
 */
function get_car($file,$cartypes){
    $data = array();
    $car_type = unpack('v', substr($file, 120, 2))[1];
    $data['id'] = $car_type;
    $data['text'] = $cartypes[$car_type];
    return $data;
}

/**
 * Extracts information track from a given file.
 * @param string $file The replay file.
 * @return array An associative array containing track information:
 *               - 'id': The track ID extracted from the file.
 *               - 'text': The descriptive text for the track.
 */
function get_track($file,$track_ids){
    $data = array();
    $track_id = unpack('v', substr($file, 36, 2))[1];
    $data['id'] = $track_id;
    $data['text'] = $track_ids[$track_id];
    return $data;
}

/**
 * Extracts information about the number of laps from a given file.
 * @param string $file The replay file.
 * @return array An associative array containing lap count information:
 *               - 'id': The lap count ID extracted from the file.
 *               - 'text': The descriptive text for the lap count.
 */
function get_lap_count($file,$lap_counts){
    $data = array();
    $lapcount_id = unpack('v', substr($file, 4, 2))[1];
    $data['id'] = $lapcount_id;
    $data['text'] = $lap_counts[$lapcount_id];
    return $data;
}

/**
 * Extracts information about the direction (forward or backward) of the track from a given file.
 * @param string $file The replay file.
 * @return array An associative array containing track direction information:
 *               - 'id': The track direction ID extracted from the file.
 *               - 'text': The descriptive text for the track direction.
 */
function get_track_direction($file,$lap_directions){
    $data = array();
    $direction_id = unpack('v', substr($file, 48, 2))[1];
    $data['id'] = $direction_id;
    $data['text'] = $lap_directions[$direction_id];
    return $data;
}

/**
 * Extracts information about the direction (mirrored or not) of the track from a given file.
 * @param string $file The replay file.
 * @return array An associative array containing track direction information:
 *               - 'id': The track direction ID extracted from the file.
 *               - 'text': The descriptive text for the track direction.
 */
function get_track_direction2($file,$lap_directions2){
    $data = array();
    $direction_id = unpack('v', substr($file, 52, 2))[1];
    $data['id'] = $direction_id;
    $data['text'] = $lap_directions2[$direction_id];
    return $data;
}

/**
 * Extracts information about the lap time from a given file. (THIS IS WRONT AT THE MOMENT)
 * @param string $file The replay file.
 * @return string The lap time in the format MM:SS.hh
 */
function get_time($file){
    $lap_time = unpack('V', substr($file, 1456, 4))[1];
    $seconds = $lap_time / 64;
    $minutes = floor($seconds / 60);
    $minutes = (int)$minutes;
    $remainingSeconds = $seconds % 60;
    $remainingSeconds = (int)$remainingSeconds;
    $hundredths = substr(str_replace('0.', '', strval(fmod($seconds, 1))), 0,2);
    $formattedLapTime = sprintf("%d:%02d.%02d", $minutes, $remainingSeconds, $hundredths);
    return $formattedLapTime;
}

/**
 * Extracts information about the cheat dedection from a given file.
 * @param string $file The replay file.
 * @return array An associative array containing cheat dedection information:
 *               - 'id': The cheat dedection ID extracted from the file.
 *               - 'text': The descriptive text for the cheat dedection.
 */
function cheat_dedection($file){
    $data = array();
    $cheat = unpack('v', substr($file, 32, 2))[1];
    $data['id'] = $cheat;
    
    if($cheat > 0){
        $data['text'] = 'Yes';
    }else{
        $data['text'] = 'No';
    }
    return $data;
}

/**
 * Extracts information about the game mode from a given file.
 * @param string $file The replay file.
 * @return array An associative array containing game mode information:
 *               - 'id': The game mode ID extracted from the file.
 *               - 'text': The descriptive text for the game mode.
 */
function get_game_mode($file,$skill_levels){
    $data = array();
    $game_mode_1 = unpack('v', substr($file, 56, 2))[1];
    $game_mode_2 = unpack('v', substr($file, 12, 2))[1];
    if ($game_mode_1 == 1 and $game_mode_2 == 1){
        $game_mode_id = 1;
    }
    if ($game_mode_1 == 0 and $game_mode_2 == 1){
        $game_mode_id = 2;
    }
    if ($game_mode_1 == 0 and $game_mode_2 == 0){
        $game_mode_id = 0;
    }
    $data['id'] = $game_mode_id;
    $data['text'] = $skill_levels[$game_mode_id];
    return $data;
}


function generateUniqueFilename($prefix = 'file_') {
    $timestamp = time(); // Current timestamp
    $randomString = bin2hex(random_bytes(8)); // Generate a random string

    // Combine timestamp and random string to create a unique filename
    $filename = $prefix . $timestamp . '_' . $randomString;

    return $filename;
}



$file_name = $_FILES['rpy']['name'];
$file_path= $_FILES['rpy']['tmp_name'];
$filesize = filesize($file_path);
$fp = fopen($file_path, 'rb');
$file = fread($fp, $filesize);
fclose($fp);
unlink($file_path);



$player = get_player_name($file);
$skill = get_game_mode($file,$skill_levels);
$car = get_car($file,$cartypes);

$track = get_track($file,$track_ids);
$lap_count = get_lap_count($file,$lap_counts);
$lap_direction = get_track_direction($file,$lap_directions);
$lap_direction2 = get_track_direction2($file,$lap_directions2);

$cheat = cheat_dedection($file);

$lap_time = get_time($file);

$data = array(
    'file' => $file_name,
    'player' => $player['text'],
    'skill' => $skill['text'],
    'skill_id' => $skill['id'],
    'car' => $car['text'],
    'car_id' => $car['id'],
    'track' => $track['text'],
    'track_id' => $track['id'],
    'lap_count' => $lap_count['text'],
    'lap_count_id' => $lap_count['id'],
    'lap_direction' => $lap_direction['text'],
    'lap_direction_id' => $lap_direction['id'],
    'lap_direction2' => $lap_direction2['text'],
    'lap_direction2_id' => $lap_direction2['id'],
    'cheat' => $cheat['text'],
    'cheat_id' => $cheat['id'],
    #'time' => $lap_time,

);


print_r($data);
