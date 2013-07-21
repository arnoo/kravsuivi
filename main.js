var changes = {};

//TODO: scroller vers $("td.future:first")

var comment_comment;
var comment_day;

$("div.button_comment").click(function (evt) {
				var old_comment_day = comment_day;
				comment_day = $(this).data("day");
				if (comment_day == old_comment_day)
					{
					$('#comment').hide();
					comment_day = null;
					return;
					}
				comment_comment = $(this).data("comment");
				$('#comment textarea').val(comment_comment);
				$('#comment').show();
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
		$.post("", changes, function () {changes = {};});
		});
