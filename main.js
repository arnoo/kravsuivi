var changes = {};

//TODO: scroller vers $("td.future:first")

var comment_comment;
var comment_button;
var comment_day = null;

function save_comment(day)
	{
	var comment = $("#comment textarea").val();
	if (comment_day!==null && comment_comment!==comment)
		{
		comment_button = this;
		$("div.button_comment[data-day='"+comment_day+"']").data("comment", comment)
								   .toggleClass("checked", comment!=='');
		if (changes["comments"]==undefined)
			{
			changes["comments"] = {};
			}
		changes["comments"][comment_day] = comment;
		}
	}

$("div.button_comment").click(function (evt) {
				save_comment();
				var old_comment_day = comment_day;
				comment_day = $(this).data("day");
				if (comment_day == old_comment_day)
					{
					$('#comment').hide();
					comment_day = null;
					return;
					}
				comment_comment = $(this).data("comment");
				comment_button = $(this);
				$("#comment textarea").val(comment_comment);
				$('#comment_date').text($(this).data("date"));
				$("#comment input[type=checkbox][name='teachers[]']").attr("checked", null);
				var teachers = $(this).data("teachers");
				for (var t in teachers)
					{
					$("#comment input[type=checkbox][value='"+t+"']").attr("checked", teachers[t] ? "checked" : null);
					}
				$('#comment').css("top", ($(window).height()/2-200)+"px")
					     .css("left", ($(window).width()/2-250)+"px")
					     .show();
				});

$("div.technique").click(function (evt)
		    		{
				var techid = $(this).data("technique_id");
				if (changes["techniques"]==undefined)
					{
					changes["techniques"] = {};
					}
				if (changes["techniques"][techid]==undefined)
					{
					changes["techniques"][techid] = {};
					}
				$(this).toggleClass("checked");
				changes["techniques"][techid][$(this).data("day")] = $(this).hasClass("checked");
				});

$("input.teacher").change(function (evt)
				{
				var teacher = $(this).attr("value");
				if (changes["teachers"]==undefined)
					{
					changes["teachers"] = {};
					}
				if (changes["teachers"][teacher]==undefined)
					{
					changes["teachers"][teacher] = {};
					}
				changes["teachers"][teacher][comment_day] = $(this).is(':checked');
				var teachers = $(comment_button).data('teachers');
				teachers[teacher] = $(this).is(':checked');
				$(comment_button).data('teachers', teachers);
				update_teachers(comment_button);
				});

$("button#save_changes").click(function (evt) {
		save_comment();
		var button = $(this);
		button.text("Sauvegarde en cours...").attr("disabled", true);
		$.post("", changes, function ()
					{
					changes = {};
					button.text("Changements sauvegardés !").attr("disabled", null);
					window.setTimeout(function () { button.text("Sauvegarder les changements"); }, 2000);
					});
		});

$("span#comment_close").click(function (evt) {
		$("div#comment").hide();
		save_comment();
		});

var syncing_scroll = false;
$(".inner").scroll(function (evt)
			{
			if (syncing_scroll) { return false; }
			syncing_scroll = true;
			var other = $('#inner_main');
			if ($(this).attr('id')=='inner_main') {
				other = $('#inner_head');
			}
			other.scrollLeft($(this).scrollLeft());
			syncing_scroll = false;
			});

$(document).ready(function ()
			{
			var height = $(window).height();
			$("#outer_main").height((height-300)+"px");
			$(".button_comment").each(function () {update_teachers(this);});
			});

function update_teachers(comment_button)
	{
	var teachers_td = $(comment_button).parent().parent().prev().find('#teachers_'+$(comment_button).data('day'));
	var teachers = $(comment_button).data("teachers");
	var teachers_short = [];
	var teachers_long = [];
	for (t in teachers)
		{
		if (teachers[t])
			{
			teachers_short.push(teacher_shortnames[t]);
			teachers_long.push(t);
			}
		}		
	$(teachers_td).text(teachers_short.join(", ")).attr("title", teachers_long.join(", "));
	}
