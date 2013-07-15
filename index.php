<?php

$lesson = array('id' => 'creteil_avance',
	        'belts' => array('yellow'),
		'name' => "Cours Avancés Créteil 2013-2014",
                'days_of_week' => array(0,3),
                'start_date' => "2013-09-10",
		'teachers' => array("+Jérome", "Cédric", "Arnaud"),
                'end_date' => "2014-06-20");

#$db_fname = 'db_'.$lesson['id'].'.sqlite3';
#if (!file_exists($db)) {
#	$db = new SQLite3($db_fname);
#	$db->exec("CREATE TABLE teachers ( day CHAR(8), teacher VARCHAR(50))");
#	$db->exec("CREATE TABLE subjects ( day CHAR(8), subject VARCHAR(50), commentary)");
#}
#$db = new SQLite3($db_fname);
#
#if ($_POST) {
#	foreach ($_POST as $name => $ok) {
#		$db->exec("DELETE FROM subjects WHERE day=?");
#
#	}
#}

#$results = $db->query('SELECT bar FROM foo');
#while ($row = $results->fetchArray()) {
#    var_dump($row);
#}	    

$belt_techniques = json_decode(file_get_contents("belts.json"), true);

print "<html><head><style type='text/css'>div#comments { display:none;}</style></head>";
print "<body>";
print "<h1>".$lesson['name']."</h1>";
	
print "<table><thead>";
print "<tr><td></td>";
$lesson_start_time = strtotime($lesson['start_date']);
$lesson_end_time = strtotime($lesson['end_date']);
$lesson_ids = array();
for ($i=0; $i<365; $i++) {
	$i_time = $lesson_start_time+$i*3600*24;
	if ($i_time>$lesson_end_time) { break; }
	if (in_array(date('', $i_time), $lesson['days_of_week'])) {
		print "<th>".date('d M', $i_time)."</th>";
		$lesson_ids[] = date('Ymd', $i_time);
	}
}
print "</tr>";
print "<tr><th>Profs</th>";
foreach($lesson_ids as $lesson_id) {
	print "<td>";
	foreach ($lesson['teachers'] as $teacher) {
		if ($teacher[0] === "+") {
			$teacher_name = substr($teacher, 1);
			$args = "checked='checked'";
		} else {
			$teacher_name = $teacher;
			$args = "";
		}
		print "<label><input $args type='checkbox'>$teacher_name</label>";
		}
	print "</td>";
}

print "</tr>";
print "</thead><tbody>";
$comment_div = "";
foreach ($lesson['belts'] as $belt) {
	foreach ($belt_techniques[$belt]['techniques'] as $technique_id => $technique) {
		print "<tr><td>";
		print $technique['fr'];
		print "</td>";
		foreach($lesson_ids as $lesson_id) {
			print "<td><input type='checkbox' name='".$lesson_id."_$technique_id'></td>";
			$comment_div .= "<div id='comment_".$lesson_id."_$technique_id'><textarea id='comment_".$lesson_id."_$technique_id'>blabla</textarea>";
		}
		print "</tr>\n";
	}
}
print "</table>\n";
print "<div id='comments'>$comment_div</div>";

?>
