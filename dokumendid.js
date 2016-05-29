$(document).ready(function(){
  $("#kuvadokumendid").click(function(){
    $("#pakkumistepaneel").fadeIn();
      $("#kuvadokumendid").css("display","none");
  });

  $(".logout").click(function(){
    confirm("Kas sa oled kindel?");
  });
});