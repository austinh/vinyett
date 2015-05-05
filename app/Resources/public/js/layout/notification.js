function accept_notification_friend_request(notification_id, request_id)
{ 
  $(".qtip .notification .button_row").fadeOut();
  $.ajax({
    url: Global.settings.rurl+'friends/requests/accept',
    data: "requester_id="+request_id+"&from_notification="+notification_id,
    type: "POST",
    success: function(data) {
      alert(data);
      var name = $(".qtip .notification #friend_request_"+notification_id+"_name").html();
      $(".qtip .notification .title").html(name+" and you are now friends. <br />");
      $(".qtip .notification .time").html("Just now");
    }, 
    error: function() { 
      alert("Oops, looks like something went wrong and your request wasn't processed. Refresh and try again?");
    }
  })
}