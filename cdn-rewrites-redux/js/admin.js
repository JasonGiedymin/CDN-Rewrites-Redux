jQuery(function($){
    $("form.cdnr_ajax").live("submit", function(e){
        e.preventDefault();
        var $f = $(this);
        goTop();
        $("#generalLoading").show();
        $.ajax({
            type: $f.attr("method"),
            url: $f.attr("action"),
            data: $f.serialize(),
            success: function(msg){
                switch($f.attr("ftype"))
                {
                    case "add":
                        $("#profilesTable tbody").prepend(msg);
                        $("#jquery-tabs").tabs("select", 0);
						updateStatus("<p>New profile added.</p>");
                        break;
                    case "edit":
                        var profileId = $f.find("input:hidden[name='id']").val();
                        $("#profile_" + profileId).replaceWith(msg);
                        $("#jquery-tabs").tabs("select", 0);
                        $("#editDiv input[name='cancel']").click();
						updateStatus("<p>Profile updated.</p>");
                        break;
                    default:
                        updateStatus(msg);
                        break;
                }
                $("#generalLoading").hide();
            },
            error: function(){
                updateStatus("<p>Error happened. Check that you filled up the mandatory fields.</p>");
                $("#generalLoading").hide();
            }
        });
    });
	
	$("#jquery-tabs").tabs();
    
    
    $("a.ajax").live("click", function(e){
        e.preventDefault();
        if ($(this).is(".need_confirm") && !confirm($(this).attr("confirmtext")))
        {
            return false;
        }

        var profileId = $(this).attr("profile");
        var action = $(this).attr("action");
        var a = this;

        $("#loading_" + profileId).fadeIn("fast", function(){
            $.ajax({
                url     : "index.php",
                type    : "post",
                data    : "id=" + profileId + "&cdnr_action=" + action + "&_nonce=" + getNonce(),
                success    : function(msg){
                    $("#loading_" + profileId).fadeOut();
                    switch(action){
                        case "activate":
                            $("#profile_" + profileId).addClass("active");
                            $(a).html(msg).attr("action", "deactivate");
							updateStatus("<p>Profile activated.</p>");
                            break;
                        case "deactivate":
                            $("#profile_" + profileId).removeClass("active");
                            $(a).html(msg).attr("action", "activate");
							updateStatus("<p>Profile deactivated.</p>");
                            break;
                        case "edit":
                            $("#editInstruction").hide();
                            $("#editDiv form").remove();
                            $("#editDiv").append(msg).slideDown("fast");
                            $("#jquery-tabs").tabs("select", 2);
                            break;
                        case "delete":
                            $("#profile_" + profileId).fadeOut("fast", function(){
								updateStatus("<p>Profile deleted.</p>");
                                $(this).remove();
                            });

                            // in case this is being edited?
                            if ($("#editDiv form").find("input:hidden[name='id']").val() == profileId)
                            {
                                $("#editDiv").slideUp("fast", function(){
                                    $("#editDiv form").remove();
                                });
                            }
                            break;
                        default:
                            break;
                    }                    
                },
                error    : function(msg){
                    updateStatus("<p>Error happened. Nothing changed.</p>");
                    $("#loading_" + profileId).fadeOut();
                }
            });
        });
    });
    
    $("#editDiv input[name='cancel']").live("click", function(e){
        e.preventDefault();
        $(this.form).remove();
        $("#editInstruction").show();
        $("#jquery-tabs").tabs("select", 0);
    });
});

function updateStatus(str)
{
	jQuery("#message").html(str).fadeIn("normal", function(){
		setTimeout(function(){
			jQuery("#message").fadeOut();
		}, 3000);
	});	
}

function goTop() 
{
    if (document.body.scrollTop != 0 || document.documentElement.scrollTop != 0){
        window.scrollBy(0, -50);
        t = setTimeout('goTop()', 10);
    }
    else clearTimeout(t);
}

function getNonce()
{
    return jQuery("input[name='_nonce']:first").val();
}