/**
 * Created by Root on 3/24/15.
 */

// Dependencies:
// jQuery.min.js
// jQuery.ui.min.js
// jQuery.horizontalScroll.js
$(document).ready(function() {
    // Define stuff here------------------------------------------------------------------------------------------------

    // Execute functions here-------------------------------------------------------------------------------------------
    $(function() {
        $("#datepicker").datepicker();
    });
    $(function() {
        $(".headNews").mousewheel(function(event, delta) {
            this.scrollLeft -= (delta * 50);
            event.preventDefault();
        });
    });
    $(function() {
       var nid = $("#newsid").attr("nid");
        $.ajax({
            beforeSend: function() {
                // Show a processing icon
            },
            type: "POST",
            timeout: 3000,
            url: "http://forty2reviews.com/lib/ajax/news.ajax.php",
            data: { action: "get_comments", id: nid, page: 1},
            success: function( msg ){
                $('#newsComments').html(msg);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });
    });
    $(function() {
        var nid = $("#reviewid").attr("rid");
        $.ajax({
            beforeSend: function() {
                // Show a processing icon
            },
            type: "POST",
            timeout: 3000,
            url: "http://forty2reviews.com/lib/ajax/review.ajax.php",
            data: { action: "get_comments", id: nid, page: 1},
            success: function( msg ){
                $('#reviewComments').html(msg);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });
    });
    $(function() {
        var type = $("div[rel*=items-list]").attr("type");
        $.ajax({
            beforeSend: function() {
                // Show a processing icon
            },
            type: "POST",
            url: "http://forty2reviews.com/lib/ajax/item.ajax.php",
            timeout: 3000,
            data: { action: "itemlist", type: type, page: 1},
            success: function( msg ){
                $('div[rel*=items-list]').html(msg);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });
    });
    // Catch stuff here-------------------------------------------------------------------------------------------------
    //---------------------------------Getting------------------------------
    // Login form-get
    $(document).on("click", "a[rel*=add-review-form]",function(event) {
        event.preventDefault();
        var itemID = $("a[rel*=add-review-form]").attr("itemid");
        $.ajax({
            beforeSend: function() {
                // Show a processing icon
            },
            type: "POST",
            url: "http://forty2reviews.com/lib/ajax/form.ajax.php",
            timeout: 3000,
            data: { form: "add-review", item: itemID}, // Make sure we also submit the item!
            success: function( msg ){
                $('#page-overlay').show();
                $('#page-popout-div').slideDown();
                $('#page-popout-div-content').html(msg).slideDown();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });
    });
    // Login form-get
    $(document).on("click", "a[rel*=login]",function(event) {
        event.preventDefault();
        $.ajax({
            beforeSend: function() {
                // Show a processing icon
            },
            type: "POST",
            url: "http://forty2reviews.com/lib/ajax/form.ajax.php",
            data: { form: "login"},
            timeout: 3000,
            success: function( msg ){
                $('#page-overlay').show();
                $('#page-popout-div').slideDown();
                $('#page-popout-div-content').html(msg).slideDown();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });
    });
    // Register form-get
    $(document).on("click", "a[rel*=register]",function(event) {
        event.preventDefault();
        $.ajax({
            beforeSend: function() {
                // Show a processing icon
            },
            type: "POST",
            url: "http://forty2reviews.com/lib/ajax/form.ajax.php",
            timeout: 3000,
            data: { form: "register"},
            success: function( msg ){
                $('#page-overlay').show();
                $('#page-popout-div').slideDown();
                $('#page-popout-div-content').html(msg).slideDown();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });
    });
    // Config
    $(document).on("click", "a[rel*=config]",function(event) {
        event.preventDefault();
        $.ajax({
            beforeSend: function() {
                // Show a processing icon
            },
            type: "POST",
            url: "http://forty2reviews.com/lib/ajax/form.ajax.php",
            timeout: 3000,
            data: { form: "config"},
            success: function( msg ){
                $('#page-overlay').show();
                $('#page-popout-div').slideDown();
                $('#page-popout-div-content').html(msg).slideDown();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });
    });
    // newscomment upboat
    $(document).on("click", "a[rel*=news-upboat]",function(event) {
        event.preventDefault();
        var cid = $(this).attr("cid");
        var com = $(this);
        $.ajax({
            beforeSend: function() {
                // Show a processing icon
            },
            type: "POST",
            url: "http://forty2reviews.com/lib/ajax/news.ajax.php",
            data: { action: "upboat", cid: cid},
            timeout: 3000,
            success: function( msg ){
                if(msg == 'fail') {
                    // Set as false
                    com.removeClass("upboated");
                }else{
                    // Set as true
                    com.addClass("upboated");
                }
                // Another AJAX call
                    $.ajax({
                        beforeSend: function() {
                        },
                        type: "POST",
                        url: "http://forty2reviews.com/lib/ajax/news.ajax.php",
                        data: { action: "get_upboats", cid: cid},
                        timeout: 3000,
                        success: function( msg ){
                            $("section[rel*='news-upboat-count'][cid*=" + cid + "]").html(msg);
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            $("section[rel*='news-upboat-count'][cid*=" + cid + "]").html('0');
                        }
                    });
                // END
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });
    });
    // News comment pages
    $(document).on("click", "a[rel*=news-comment-page]",function(event) {
        var con = $(this);
        var page = $(this).attr("page");
        var nid = $(this).attr("nid");
        event.preventDefault();
        $.ajax({
            beforeSend: function() {
                // Show a processing icon
            },
            type: "POST",
            url: "http://forty2reviews.com/lib/ajax/news.ajax.php",
            timeout: 3000,
            data: { action: "get_comments", id: nid, page: page},
            success: function( msg ){
                $('#newsComments').html(msg);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });
    });
    // reviewcomment upboat
    $(document).on("click", "a[rel*=review-upboat]",function(event) {
        event.preventDefault();
        var cid = $(this).attr("cid");
        var com = $(this);
        $.ajax({
            beforeSend: function() {
                // Show a processing icon
            },
            type: "POST",
            url: "http://forty2reviews.com/lib/ajax/review.ajax.php",
            data: { action: "upboat", cid: cid},
            timeout: 3000,
            success: function( msg ){
                if(msg == 'fail') {
                    // Set as false
                    com.removeClass("upboated");
                }else{
                    // Set as true
                    com.addClass("upboated");
                }
                // Another AJAX call
                $.ajax({
                    beforeSend: function() {
                    },
                    type: "POST",
                    url: "http://forty2reviews.com/lib/ajax/review.ajax.php",
                    data: { action: "get_upboats", cid: cid},
                    timeout: 3000,
                    success: function( msg ){
                        $("section[rel*='review-upboat-count'][cid*=" + cid + "]").html(msg);
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        $("section[rel*='review-upboat-count'][cid*=" + cid + "]").html('0');
                    }
                });
                // END
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });
    });
    // review comment pages
    $(document).on("click", "a[rel*=review-comment-page]",function(event) {
        var com = $(this);
        var page = $(this).attr("page");
        var rid = $(this).attr("rid");
        event.preventDefault();
        $.ajax({
            beforeSend: function() {
                // Show a processing icon
            },
            type: "POST",
            url: "http://forty2reviews.com/lib/ajax/review.ajax.php",
            data: { action: "get_comments", id: rid, page: page},
            timeout: 3000,
            success: function( msg ){
                $('#reviewComments').html(msg);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });
    });
    // Item list page
    $(document).on("click", "a[rel*=item-list-page]",function(event) {
        var page = $(this).attr("page");
        var type = $(this).attr("type");
        event.preventDefault();
        $.ajax({
            beforeSend: function() {
                // Show a processing icon
            },
            type: "POST",
            url: "http://forty2reviews.com/lib/ajax/item.ajax.php",
            data: { action: "itemlist", type: type, page: page},
            timeout: 3000,
            success: function( msg ){
                $('div[rel*=items-list]').html(msg);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                if(textStatus === 'timeout') {
                    alert('Connection timed out.');
                }
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });
    });
    //--------------------------------Submitting-----------------------------------
    // review form-submit
    $(document).on("click", "a[rel*=review-submit]",function(e) {
        e.preventDefault();
        $.ajax({
            beforeSend: function() {
                $('#add-review-form-form').append('<div class="message processing"><h3>Processing <i class="fa fa-spinner fa-pulse"></i></h3></div>');
            },
            type: "POST",
            url: "http://forty2reviews.com/lib/ajax/review.ajax.php",
            timeout: 3000,
            data: $('#add-review-form-form').serializeArray(),
            success: function( msg ){
                // var obj = jQuery.parseJSON( '{ "name": "John" }' );
                // alert( obj.name === "John" );
                var obj = jQuery.parseJSON( msg );

                if(obj.status !== 'success') {
                    $('#add-review-form-form').append('<div class="message ' + obj.status + '"><h3>' + obj.message + '</h3></div>');
                    $('#add-review-form-form > .message.error').delay(2000).slideUp();
                }else{
                    $('#add-review-form-form').append('<div class="message succes"><h3>Successfully submitted review !</h3></div>');
                    setTimeout(function () {/* Re-direct to the new review! */ location.replace(obj.url); }, 800);
                }
                $("#add-review-form-form > .message.processing").slideUp();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                if(textStatus === 'timeout') {
                    $('#add-review-form-form').append('<div class="message ' + obj.status + '"><h3>Connection timed out.</h3></div>');
                    $('#add-review-form-form > .message.error').delay(2000).slideUp();
                }
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });
    });
    // Login form-submit
    $(document).on("click", "a[rel*=login-submit]",function(e) {
        e.preventDefault();
        $.ajax({
            beforeSend: function() {
                $('#login-form').append('<div class="message processing"><h3>Processing <i class="fa fa-spinner fa-pulse"></i></h3></div>');
            },
            type: "POST",
            url: "http://forty2reviews.com/lib/ajax/user.ajax.php",
            timeout: 3000,
            data: $('#login-form').serializeArray(),
            success: function( msg ){
                if(msg !== 'success') {
                    $('#login-form').append('<div class="message error"><h3>' + msg + '</h3></div>');
                    $('#login-form > .message.error').delay(2000).slideUp();
                }else{
                    $('#login-form').append('<div class="message succes"><h3>Successfully logged in!</h3></div>');
                    setTimeout(function () { location.reload(true); }, 800);
                }
                $("#login-form > .message.processing").slideUp();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                if(textStatus === 'timeout') {
                    $('#login-form').append('<div class="message ' + obj.status + '"><h3>Connection timed out.</h3></div>');
                    $('#login-form > .message.error').delay(2000).slideUp();
                }
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });
    });
    // Register form-submit
    $(document).on("click", "a[rel*=register-submit]",function(e) {
        e.preventDefault();
        $.ajax({
            beforeSend: function() {
                $('#register-form').append('<div class="message processing"><h3>Processing <i class="fa fa-spinner fa-pulse"></i></h3></div>');
            },
            type: "POST",
            url: "http://forty2reviews.com/lib/ajax/user.ajax.php",
            data: $('#register-form').serializeArray(),
            timeout: 3000,
            success: function( msg ){
                if(msg !== 'success') {
                    $('#register-form').append('<div class="message error"><h3>' + msg + '</h3></div>');
                    $('#register-form > .message.error').delay(2000).slideUp();
                }else{
                    $('#register-form').append('<div class="message succes"><h3>Account created!</h3></div>').append('<div class="message succes"><h3>Please activate your account with the mail you have received.</h3></div>');
                }
                $("#register-form > .message.processing").slideUp();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                if(textStatus === 'timeout') {
                    $('#register-form').append('<div class="message ' + obj.status + '"><h3>Connection timed out.</h3></div>');
                    $('#register-form > .message.error').delay(2000).slideUp();
                }
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });
    });
    // Config form submit
    $(document).on("click", "a[rel*=config-submit]",function(e) {
        e.preventDefault();
        $.ajax({
            beforeSend: function() {
                $('#config-form').append('<div class="message processing"><h3>Processing <i class="fa fa-spinner fa-pulse"></i></h3></div>');
            },
            type: "POST",
            url: "http://forty2reviews.com/lib/ajax/user.ajax.php",
            data: $('#config-form').serializeArray(),
            timeout: 3000,
            success: function( msg ){
                if(msg !== 'success') {
                    $('#config-form').append('<div class="message error"><h3>' + msg + '</h3></div>');
                    $('#config-form > .message.error').delay(2000).slideUp();
                }else{
                    $('#config-form').append('<div class="message succes"><h3>Configuration updated!</h3></div>');
                    setTimeout(function () { location.reload(true); }, 1000);
                }
                $("#config-form > .message.processing").slideUp();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                if(textStatus === 'timeout') {
                    $('#config-form').append('<div class="message ' + obj.status + '"><h3>Connection timed out.</h3></div>');
                    $('#config-form > .message.error').delay(2000).slideUp();
                }
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });
    });
    // News comments submit
    $(document).on("click", "a[rel*=news-comment-submit]",function(e) {
        e.preventDefault();
        $.ajax({
            beforeSend: function() {
            },
            type: "POST",
            url: "http://forty2reviews.com/lib/ajax/news.ajax.php",
            timeout: 3000,
            data: $('#news-comment-add').serializeArray(),
            success: function( msg ){
                if(msg == "success"){
                    // Added, I guess?
                    //AJAX------------
                        var nid = $("#newsid").attr("nid");
                        $.ajax({
                            beforeSend: function() {
                                // Show a processing icon
                            },
                            type: "POST",
                            url: "http://forty2reviews.com/lib/ajax/news.ajax.php",
                            data: { action: "get_comments", id: nid, page: "last"},
                            timeout: 3000,
                            success: function( msg ){
                                $('#newsComments').html(msg);
                            },
                            error: function(XMLHttpRequest, textStatus, errorThrown) {
                                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
                            }
                        });
                    //END------------
                    $('#news-comment-add').append('<div class="row deleteThis"><div class="col-4"><br></div><div class="col-4"><div class="message succes"><h3>Comment posted!</h3></div></div><div class="col-4"><br></div></div>');
                    $('.deleteThis').delay(2000).slideUp();
                }else{
                    // Failed to add
                    $('#news-comment-add').append('<div class="row deleteThis"><div class="col-4"><br></div><div class="col-4"><div class="message error"><h3>' + msg + '</h3></div></div><div class="col-4"><br></div></div>');
                    $('.deleteThis').delay(2000).slideUp();
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                if(textStatus === 'timeout') {
                    $('#news-comment-add').append('<div class="message ' + obj.status + '"><h3>Connection timed out.</h3></div>');
                    $('#news-comment-add > .message.error').delay(2000).slideUp();
                }
                $("#page-popout-spinner").fadeOut(50);
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });
    });
    // Review comments submit
    $(document).on("click", "a[rel*=review-comment-submit]",function(e) {
        e.preventDefault();
        $.ajax({
            beforeSend: function() {
            },
            type: "POST",
            url: "http://forty2reviews.com/lib/ajax/review.ajax.php",
            timeout: 3000,
            data: $('#review-comment-add').serializeArray(),
            success: function( msg ){
                if(msg == "success"){
                    // Added, I guess?
                    //AJAX------------
                    var rid = $("#reviewid").attr("rid");
                    $.ajax({
                        beforeSend: function() {
                            // Show a processing icon
                        },
                        type: "POST",
                        url: "http://forty2reviews.com/lib/ajax/review.ajax.php",
                        data: { action: "get_comments", id: rid, page: "last"},
                        timeout: 3000,
                        success: function( msg ){
                            $('#reviewComments').html(msg);
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
                        }
                    });
                    //END------------
                    $('#review-comment-add').append('<div class="row deleteThis"><div class="col-4"><br></div><div class="col-4"><div class="message succes"><h3>Comment posted!</h3></div></div><div class="col-4"><br></div></div>');
                    $('.deleteThis').delay(2000).slideUp();
                }else{
                    // Failed to add
                    $('#review-comment-add').append('<div class="row deleteThis"><div class="col-4"><br></div><div class="col-4"><div class="message error"><h3>' + msg + '</h3></div></div><div class="col-4"><br></div></div>');
                    $('.deleteThis').delay(2000).slideUp();
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                if(textStatus === 'timeout') {
                    $('#review-comment-add').append('<div class="message ' + obj.status + '"><h3>Connection timed out.</h3></div>');
                    $('#review-comment-add > .message.error').delay(2000).slideUp();
                }
                $("#page-popout-spinner").fadeOut(50);
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });
    });
    //---------------------------------Misc---------------------------------\
    // Getting The Token
    function getToken() {
        var rtn;
        $.ajax({
            beforeSend: function() {
            },
            type: "POST",
            async: false,
            url: "http://forty2reviews.com/lib/ajax/user.ajax.php",
            timeout: 3000,
            data: { action : "getToken" },
            success: function( msg ){
                rtn = msg;
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                rtn = false;
            }
        });
        return rtn;
    }
    // Getting notifications
    $keepingVar = 0;
    function getNotified($firsttime) {
        $.ajax({
            beforeSend: function() {
            },
            type: "POST",
            url: "http://forty2reviews.com/lib/ajax/user.ajax.php",
            data: { action : "user_tick" },
            timeout: 3000,
            success: function( msg ){
                console.log(msg);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });

        $.ajax({
            beforeSend: function() {
            },
            type: "POST",
            url: "http://forty2reviews.com/lib/ajax/user.ajax.php",
            data: { action : "getNotified" },
            timeout: 3000,
            success: function( msg ){
                if(msg > 0){
                    if($keepingVar != msg){
                        $("#liveMessageBox").html(msg);
                        $("#liveMessageBox").show();
                        $("#liveMessageBox").addClass('animated bounceIn');
                        if(getToken() == token){
                            if($firsttime != true){
                                document.getElementById('audiotag1').play();
                            }
                        }
                        setTimeout( function(){
                            $("#liveMessageBox").removeClass('animated bounceIn');
                        }, 1000 );

                    }
                    $keepingVar = msg;
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });
    }
    getNotified(true);
    setInterval(getNotified, 5000);

    // Logout
    $(document).on("click", "a[rel*=logout]",function(e) {
        e.preventDefault();
        $.ajax({
            beforeSend: function() {
            },
            type: "POST",
            url: "http://forty2reviews.com/lib/ajax/user.ajax.php",
            timeout: 3000,
            data: { action : "logout" },
            success: function( msg ){
                setTimeout(function () { location.reload(true); }, 1);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert("Connection timed out.");
                console.log("Status: " + textStatus + " - " + "Error: " + errorThrown);
            }
        });
    });
    // News item
    $(document).on("click", "div[rel*=news-click]",function(e) {
        e.preventDefault();
        var newsID = $(this).attr("news");
        setTimeout(function () { window.location.replace("http://forty2reviews.com/news/" + newsID); }, 50);
    });


    // Handles closing the overlay
    $("#page-overlay").on("click", function(event){
        event.preventDefault();
        $('#page-overlay').hide();
        $('#page-popout-div').slideUp(500);
    });
    $("a[rel*=close-popout]").on("click", function(event){
        event.preventDefault();
        $('#page-overlay').hide();
        $('#page-popout-div').slideUp(500);
    });
    $(document).on("click", "html",function(e) {
        if(e.target == document.getElementById("cog-click")) {
            //$("#popup-cog").slideToggle();
            if($("#popup-cog").css("display") == "none") {
                // Show
                $("#popup-cog").slideDown();
                $("#user-setting").css("background-color","#000");
            }else{
                // Hide
                $("#popup-cog").slideUp();
                $("#user-setting").css("background-color","#2d2d2d");
            }
        }else{
            $("#popup-cog").slideUp();
            $("#user-setting").css("background-color","#2d2d2d");
        }
    });
});