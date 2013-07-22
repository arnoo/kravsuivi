var changes = {};

//TODO: scroller vers $("td.future:first")

var comment_comment;
var comment_day = null;

function save_comment(day)
	{
	var comment = $("#comment textarea").val();
	if (comment_day!==null && comment_comment!==comment)
		{
		$("div.button_comment[data-day='"+comment_day+"']").data("comment", comment);
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
				$("#comment textarea").val(comment_comment);
				$('#comment_date').text($(this).data("date"));
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
				var teacher = $(this).data("teacher");
				if (changes["teachers"]==undefined)
					{
					changes["teachers"] = {};
					}
				if (changes["teachers"][teacher]==undefined)
					{
					changes["teachers"][teacher] = {};
					}
				changes["teachers"][teacher][$(this).data("day")] = $(this).is(':checked');
				});

$("button#save_changes").click(function (evt) {
		save_comment();
		$.post("", changes, function () {changes = {};});
		});

$("span#comment_close").click(function (evt) {
		$("div#comment").hide();
		save_comment();
		});

