<?php

$lesson = array('id' => 'creteil_avance',
	        'belts' => array('yellow'),
		'name' => "Cours Avancés Créteil 2013-2014",
                'days_of_week' => array(0,3),
                'start_date' => "2013-09-10",
		'teachers' => array("Jérôme", "Cédric", "Arnaud"),
                'end_date' => "2014-06-20");

$db_fname = 'db_'.$lesson['id'].'.sqlite3';
if (!file_exists($db_fname)) {
	$db = new SQLite3($db_fname, SQLITE3_OPEN_CREATE);
	$db->exec("CREATE TABLE teachers ( day CHAR(8), teacher VARCHAR(50))");
	$db->exec("CREATE TABLE techniques ( day CHAR(8), technique_id VARCHAR(50))");
	$db->exec("CREATE TABLE comments ( day CHAR(8), technique_id VARCHAR(50), comment TEXT)");
}
$db = new SQLite3($db_fname);

if ($_POST['changes'])
	{
	foreach ($_POST as $name => $value)
		{
		$parts = explode($name, '_');
		if ($name[0]=="-")
			{
			$parts[0] = substr($parts, 1);
			}
		if ($parts[0]=='techniques')
			{
			$day = $parts[1];
			$techid = implode("_", array_slice($parts[1]));
			if ($name[0]=="-")
				{
				$stmt = $db->prepare("DELETE FROM tehniques WHERE day=:day AND technique_id=:techid");
				}
			else
				{
				$stmt = $db->prepare("INSERT INTO tehniques (day, technique_id) VALUES (:day,:techid)");
				}
			$stmt->bindValue(':day', $day);
			$stmt->bindValue(':techid', $techid);
			$stmt->execute();
			}
		else if ($parts[0]=='teachers')
			{
			$day = $parts[1];
			$teacher = $value;
			if ($name[0]=="-")
				{
				$db->exec("DELETE FROM teachers WHERE day=:day AND teacher=:teacher");
				}
			else
				{
				$stmt = $db->prepare("INSERT INTO teachers (day, teacher) VALUES (:day,:teacher)");
				}
			$stmt->bindValue(':day', $day);
			$stmt->bindValue(':day', $day);
			$stmt->bindValue(':teacher', $teacher);
			$stmt->execute();
			}
		else if ($parts[0]=='comments')
			{
			$day = $parts[1];
			$techid = implode("_", array_slice($parts[1]));
			$comment = $value;
			if ($name[0]=="-")
				{
				$db->exec("DELETE FROM teachers WHERE day=:day AND teacher=:teacher");
				}
			else
				{
				$stmt = $db->prepare("INSERT INTO comments (day, technique_id, comment) VALUES (:day, :techid, :comment)");
				}
			$stmt->bindValue(':day', $day);
			$stmt->bindValue(':techid', $techid);
			$stmt->bindValue(':comment', $comment);
			$stmt->execute();
			}
		}
	}

$stmt_technique = $db->prepare('SELECT * FROM technique WHERE day=:day AND technique_id=:techid');
$stmt_teacher = $db->prepare('SELECT * FROM teacher WHERE day=:day AND teacher=:teacher');
$stmt_comment = $db->prepare('SELECT * FROM comment WHERE day=:day AND technique_id=:techid');

$belt_techniques = json_decode(file_get_contents("belts.json"), true);

?>
<html>
	<head>
		<title><?php $lesson['name'] ?></title>
		<style type='text/css'>
			div#comments { display:none;}
			div#comments div { z-index:10; position: absolute; top: 40%; left:40%; width: 20%; height:20%;}
			html, body { width: 100%; padding: 0; margin: 0;}
			table { width: 90%; overflow-x: auto;}
		</style>
		<script src="jquery-1.7.1.js"></script>
	</head>
	<body>
	<h1><?php $lesson['name'] ?></h1>

	<table><thead>
		<tr><td></td>
<?php
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
foreach($lesson_ids as $lesson_id)
	{
	print "<td>";
	$stmt_teacher->bindParam(':day', $lesson_id);
	foreach ($lesson['teachers'] as $teacher)
		{
		$stmt_teacher->bindParam(':teacher', $teacher);
		$result_teacher = $stmt_teacher->execute();
		if ($result_teacher->fetchArray())
			{
			$args = "checked='checked'";
			}
		else
			{
			$args = "";
			}
		print "<label><input name='teachers_$lesson_id' value='$teacher' $args type='checkbox'>$teacher_name</label>";
		}
	print "</td>";
	}

print "</tr>";
print "</thead><tbody>";
$comment_div = "";
foreach ($lesson['belts'] as $belt)
	{
	foreach ($belt_techniques[$belt]['techniques'] as $technique_id => $technique)
		{
		$stmt_technique->bindParam(':techid', $technique_id);
		$stmt_comment->bindParam(':techid', $technique_id);
		print "<tr><td>";
		print $technique['fr'];
		print "</td>";
		foreach($lesson_ids as $lesson_id)
			{
			$stmt_technique->bindParam(':day', $lesson_id);
			$stmt_comment->bindParam(':day', $lesson_id);
			$result_technique = $stmt_technique->execute();
			$result_comment = $stmt_comment->execute();
			print "<td>";
			print "<input type='checkbox' name='techniques_$lesson_id' value='$technique_id'";
			if ($result_technique->fetchArray())
				{
				print " checked='checked'";
				}
			print ">";
			print "<img class='button_comment' src='' alt='c'>";
			print "</td>";
			$comment_div .= "<div id='comment_".$lesson_id."_$technique_id'><textarea name='comment_".$lesson_id."_$technique_id'>";

			if ($comment = $result_comment->fetchArray())
				{
				print htmlspecialchars($comment['comment']);
				}
			print "</textarea>";
			}
		print "</tr>\n";
		}
	}
print "</table>\n";
print "<div id='comments'>$comment_div</div>";

?>

<script type='text/javascript'>
var open_comment_div;
$(".button_comment").click(function () {
				open_comment_div.hide();
				var id=$(this).prev("input").attr("name");
				open_comment_div = $("div#comment_"+id);
				open_comment_div.show();
				});
</script>
