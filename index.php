<?php

$lesson = array('id' => 'creteil_avance',
	        'belts' => array('yellow'),
		'name' => "Cours Avancés Créteil 2013-2014",
                'days_of_week' => array(2,4),
                'start_date' => "2013-09-10",
		'teachers' => array("Jérôme", "Cédric", "Arnaud"),
                'end_date' => "2014-06-20");

$db_fname = 'db_'.$lesson['id'].'.sqlite3';
if (!file_exists($db_fname)) {
	$db = new SQLite3($db_fname);
	$db->exec("CREATE TABLE teachers ( day CHAR(8), teacher VARCHAR(50), PRIMARY KEY (day, teacher))");
	$db->exec("CREATE TABLE techniques ( day CHAR(8), technique_id VARCHAR(50), PRIMARY KEY (day, technique_id))");
	$db->exec("CREATE TABLE comments ( day CHAR(8) PRIMARY KEY, comment TEXT)");
}
$db = new SQLite3($db_fname);

if ($_POST)
	{
	foreach ($_POST as $table => $changes)
		{
		if ($table=='techniques')
			{
			foreach ($changes as $techid => $techchange)
				{
				foreach ($techchange as $day => $on_off)
					{
					$stmt = $db->prepare("DELETE FROM techniques WHERE day=:day AND technique_id=:techid");
					$stmt->bindValue(':day', $day);
					$stmt->bindValue(':techid', $techid);
					$stmt->execute();
					if ($on_off=="true")
						{
						$stmt = $db->prepare("INSERT INTO techniques (day, technique_id) VALUES (:day,:techid)" );
						$stmt->bindValue(':day', $day);
						$stmt->bindValue(':techid', $techid);
						$stmt->execute();
						}
					}
				}
			}
		else if ($table=='teachers')
			{
			foreach ($changes as $teacher => $teachchange)
				{
				foreach ($teachchange as $day => $on_off)
					{
					$stmt = $db->prepare("DELETE FROM teachers WHERE day=:day AND teacher=:teacher");
					$stmt->bindValue(':day', $day);
					$stmt->bindValue(':teacher', $teacher);
					$stmt->execute();
					if ($on_off=="true")
						{
						$stmt = $db->prepare("INSERT INTO teachers (day, teacher) VALUES (:day,:teacher)");
						$stmt->bindValue(':day', $day);
						$stmt->bindValue(':teacher', $teacher);
						$stmt->execute();
						}
					}
				}
			}
		else if ($table=='comments')
			{
			foreach ($changes as $day => $comment)
				{
				$stmt = $db->prepare("DELETE FROM comments WHERE day=:day");
				$stmt->bindValue(':day', $day);
				$stmt->bindValue(':comment', $comment);
				$stmt->execute();
				if (preg_match("/^\s*$/", $comment))
					{
					$stmt = $db->prepare("INSERT INTO comments (day, comment) VALUES (:day, :comment)");
					$stmt->bindValue(':day', $day);
					$stmt->bindValue(':comment', $comment);
					$stmt->execute();
					}
				}
			}
		}
	}

$sql_technique = 'SELECT * FROM techniques WHERE day=:day AND technique_id=:techid';
$sql_teacher = 'SELECT * FROM teachers WHERE day=:day AND teacher=:teacher';
$sql_comment = 'SELECT * FROM comments WHERE day=:day';

$belt_techniques = json_decode(file_get_contents("belts.json"), true);

?>
<html>
	<head>
		<title><?php echo $lesson['name']; ?></title>
		<style type='text/css'>
			html, body { width: 100%; padding: 0; margin: 0;}

			#main_table {
			  table-layout: fixed; 
			  width: 100%;
			  *margin-left: -50px;/*ie7*/
			  height: 80%;
			}
			td, th {
			  vertical-align: top;
			  border-top: 1px solid #ccc;
			  padding:10px;
			  width:50px;
			}
			td.future { background-color: grey; }	
			th {
			  position:absolute;
			  *position: relative; /*ie7*/
			  left:0; 
			  width:50px;
			}
			.outer {position:relative}
			.inner {
				  overflow-x:scroll;
				    overflow-y:visible;
				    width:90%; 
				      margin-left:100px;
			}
			.button_comment {
				width: 40px;
				height: 40px;
				background-image: url('uncomment.png');
				background-size: 40px 40px;
			}
			.button_comment.checked {
				background-image: url('comment.png');
				background-size: 40px 40px;
			}
			.technique {
				width: 40px;
				height: 40px;
				background-image: url('unchecked.png');
				background-size: 40px 40px;
			}
			.technique.checked {
				background-image: url('checked.png');
				background-size: 40px 40px;
			}
			div#comment {
				display: none;
				position: absolute;
				left:	30%;
				top:	30%;
				width:	40%;
				height:	40%;
				z-index: 30;
				border: 2px solid black;
				border-radius: 5px;
			}
		</style>
		<script src="jquery-1.7.1.js"></script>
	</head>
	<body>
	<h1><?php echo $lesson['name']; ?></h1>

	<div class='outer'><div class='inner'>
	<table id="main_table"><thead>
		<tr><th></th>
<?php
$lesson_start_time = strtotime($lesson['start_date']);
$lesson_end_time = strtotime($lesson['end_date']);
$lesson_ids = array();
for ($i=0; $i<365; $i++) {
	$i_time = $lesson_start_time+$i*3600*24;
	if ($i_time>$lesson_end_time) { break; }
	if (in_array(date('N', $i_time), $lesson['days_of_week'])) {
		print "<td";
		if ($i_time>time())
			{
			print " class='future'";
			}
		print ">".date('D d M', $i_time)."</td>";
		$lesson_ids[] = date('Ymd', $i_time);
	}
}
print "</tr>";
print "<tr><th>Profs</th>";
foreach($lesson_ids as $lesson_id)
	{
	print "<td>";
	foreach ($lesson['teachers'] as $teacher)
		{
		$stmt_teacher = $db->prepare($sql_teacher);
		$stmt_teacher->bindValue(':day', $lesson_id);
		$stmt_teacher->bindValue(':teacher', $teacher);
		$result_teacher = $stmt_teacher->execute();
		if ($result_teacher->fetchArray())
			{
			$args = "checked='checked'";
			}
		else
			{
			$args = "";
			}
		print "<label><input class='teacher' data-day='$lesson_id' data-teacher='$teacher' $args type='checkbox'>$teacher</label>";
		}
	print "</td>";
	}

print "</tr>";
print "<tr><th>Commentaires généraux</th>";
foreach ($lesson_ids as $lesson_id)
	{
	$stmt_comment = $db->prepare($sql_comment);
	$stmt_comment->bindValue(':day', $lesson_id);
	$result_comment = $stmt_comment->execute();
	print "<td><div class='button_comment";
	if ($comm = $result_comment->fetchArray())
		{
		print " checked' data-comment='".htmlspecialchars($comm['comment']);
		}
	print "' data-day='$lesson_id'>&nbsp;</div></td>";
	}
print "</thead><tbody>";
foreach ($lesson['belts'] as $belt)
	{
	foreach ($belt_techniques[$belt]['techniques'] as $technique_id => $technique)
		{
		print "<tr><th>";
		print "<div style='float:left; width:10px; background-color:$belt;'>&nbsp;</div>";
		print $technique['fr'];
		print "</th>";
		foreach($lesson_ids as $lesson_id)
			{
			$stmt_technique = $db->prepare($sql_technique);
			$stmt_technique->bindValue(':techid', $technique_id);
			$stmt_technique->bindValue(':day', $lesson_id);
			$result_technique = $stmt_technique->execute();
			print "<td>";
			print "<div data-technique_id='$technique_id' data-day='$lesson_id' class='technique";
			if ($result_technique->fetchArray())
				{
				print " checked";
				}
			print "'>&nbsp;</div>";
			print "</td>";
			}
		print "</tr>\n";
		}
	}
print "</table></div></div>\n";
print "<button id='save_changes'>Sauvegarder les changements</button>";
print "<div id='comment'><textarea></textarea></div>";

?>

<script type='text/javascript' src='main.js'></script>
