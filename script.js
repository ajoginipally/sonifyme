/*
could have used the jQuery shorthand for this, but I'm not sure why I'd want
 to when this works just fine
*/
document.getElementById("progressQuiz").addEventListener("click", function(){
   /*toggle the two quizzes, while still keeping the elements in the DOM*/
   document.getElementById("firstQuiz").classList.toggle("hide");
   document.getElementById("secondQuiz").classList.toggle("hide");
});
document.getElementById("submitButton").addEventListener("click",function(){
   /* toggle the result content and landing page*/
   document.getElementById("main").classList.toggle("hide");
   document.getElementById("launcher").classList.toggle("hide");
   document.getElementById("page-1").classList.toggle("hide");
   document.getElementById("landingTitle").classList.toggle("hide");
});
