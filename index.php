<?php

$lessons = array('creteil_avance_2013' => array('belts' => array('yellow'),
						'name' => "Cours Avancés Créteil 2013-2014",
                				'days_of_week' => array(2,4),
                				'start_date' => "2013-09-10",
						'end_date' => "2014-06-20",
						'teachers' => array("Jérôme", "Cédric", "Arnaud"),
						'locale' => 'fr_FR',
						));

if (!isset($_GET['cours']))
	{
	print "Id cours manquant";
	die;
	}
else
	{
	$lesson_id = $_GET['cours'];
	if (!isset($lessons[$lesson_id]))
		{
		print "Cours '$lesson_id' introuvable";
		die;
		}
	$lesson = $lessons[$lesson_id];
	}

setlocale(LC_TIME, $lesson['locale'].'.utf8');

$db_fname = 'db_'.$lesson_id.'.sqlite3';
if (!file_exists($db_fname))
	{
	$db = new SQLite3($db_fname);
	$db->exec("CREATE TABLE teachers ( day CHAR(8), teacher VARCHAR(50), PRIMARY KEY (day, teacher))");
	$db->exec("CREATE TABLE techniques ( day CHAR(8), technique_id VARCHAR(50), PRIMARY KEY (day, technique_id))");
	$db->exec("CREATE TABLE comments ( day CHAR(8) PRIMARY KEY, comment TEXT)");
	}
$db = new SQLite3($db_fname);

function teacher_unique_name($teacher)
	{
	global $lesson;

	$matches = 999;
	$uname = '';
	while ($matches > 1)
		{
		$uname = ucfirst(substr($teacher, 0, strlen($uname)+1));
		$matches = 0;
		foreach ($lesson['teachers'] as $other)
			{
			if (ucfirst(substr($other, 0, strlen($uname)))==$uname)
				{
				$matches++;
				}
			}
		}
	return $uname;
	}

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
				$stmt->execute();
				if (!preg_match("/^\s*$/", $comment))
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
$sql_comment = 'SELECT * FROM comments WHERE day=:day';

$belt_techniques = json_decode(file_get_contents("belts/belts.json"), true);

?>
<html>
	<head>
		<title><?php echo $lesson['name']; ?></title>
		<script src="jquery-1.7.1.js"></script>
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
	<button id='save_changes'>Sauvegarder les changements</button>
	<h1><?php echo $lesson['name']; ?></h1>

	<div id='outer_head'><div id='inner_head' class='inner'>
	<table id="head_table"><thead>
		<tr><th></th>
<?php

function teachers_day($day)
	{
	global $db;
	global $lesson;

	$sql_teacher = 'SELECT * FROM teachers WHERE day=:day AND teacher=:teacher';

	$teachers = array();
	foreach ($lesson['teachers'] as $teacher)
		{
		$stmt_teacher = $db->prepare($sql_teacher);
		$stmt_teacher->bindValue(':day', $day);
		$stmt_teacher->bindValue(':teacher', $teacher);
		$result_teacher = $stmt_teacher->execute();
		if ($result_teacher->fetchArray())
			{
			$teachers[] = $teacher;
			}
		}
	return $teachers;
	}

$lesson_start_time = strtotime($lesson['start_date']);
$lesson_end_time = strtotime($lesson['end_date']);
$lesson_ids = array();
for ($i=0; $i<365; $i++)
	{
	$i_time = $lesson_start_time+$i*3600*24;
	if ($i_time>$lesson_end_time) { break; }
	if (in_array(date('N', $i_time), $lesson['days_of_week']))
		{
		print "<td";
		if ($i_time>time())
			{
			print " class='future'";
			}
		print ">".strftime('%a %e %b', $i_time)."</td>";
		$lesson_ids[] = date('Ymd', $i_time);
		}
	}
print "</tr>";
print "<tr><th>Profs</th>";
foreach($lesson_ids as $lesson_id)
	{
	print "<td id='teachers_$lesson_id'>&nbsp;</td>";
	}

print "</tr>";
print "<tr><th>Commentaires généraux</th>";
foreach ($lesson_ids as $lesson_id)
	{
	$stmt_comment = $db->prepare($sql_comment);
	$stmt_comment->bindValue(':day', $lesson_id);
	$result_comment = $stmt_comment->execute();
	print "<td><div title='Cliquer pour voir/modifier le commentaire' class='button_comment";
	if ($comm = $result_comment->fetchArray())
		{
		print " checked' data-comment='".htmlspecialchars($comm['comment']);
		}
	print "' data-teachers='{";
	$teachers_for_day = teachers_day($lesson_id);
	$teachers_string = "";
	foreach ($lesson['teachers'] as $teacher)
		{
		$teachers_string .= '"'.$teacher.'" : '.(in_array($teacher, $teachers_for_day) ? 'true' : 'false').', ';
		}
	print substr($teachers_string, 0, strlen($teachers_string)-2);
	print "}' data-date='".strftime('%a %e %b', strtotime($lesson_id))."' data-day='$lesson_id'>&nbsp;</div></td>";
	}
print "</thead></table></div></div>
	<div id='outer_main'><div id='inner_main' class='inner'>
	<table id='main_table'><tbody>";
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
print "<div id='comment'><span id='comment_close'>x</span><h2>Commentaire pour le <span id='comment_date'>22 avril</span></h2><textarea></textarea><h2>Profs</h2>";
foreach ($lesson['teachers'] as $teacher)
	{
	print "<label><input class='teacher' type='checkbox' name='teachers[]' value='$teacher'>$teacher</label>";
	}
"</div>";

?>
<script type='text/javascript'>
var teacher_shortnames = {
<?php
$teachers_string = "";
foreach ($lesson['teachers'] as $teacher)
	{
	$teachers_string .= '"'.$teacher.'" : "'.teacher_unique_name($teacher).'", ';
	}
print substr($teachers_string, 0, strlen($teachers_string)-2);
?>
};
</script>

<script type='text/javascript' src='main.js'></script>
