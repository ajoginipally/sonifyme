document.getElementById("progressQuiz").addEventListener("click", function(){
   //toggle quiz heights
   document.getElementById("firstQuiz").classList.toggle("hide");
   document.getElementById("secondQuiz").classList.toggle("hide");
});
document.getElementById("submitButton").addEventListener("click",function(){
   document.getElementById("main").classList.toggle("hide");
   document.getElementById("launcher").classList.toggle("hide");
   document.getElementById("page-1").classList.toggle("hide");
   document.getElementById("landingTitle").classList.toggle("hide");
});