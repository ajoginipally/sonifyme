/*
could have used the jQuery shorthand for this, but I'm not sure why I'd want
 to when this works just fine
*/

/**toggle the two quizzes, while still keeping the elements in the DOM*/
document.getElementById("progressQuiz").addEventListener("click", function () {
   

document.getElementById("firstQuiz").classList.toggle("hide");
   document.getElementById("secondQuiz").classList.toggle("hide");
});

document.getElementById("submitButton").addEventListener("click", function(){
   /* toggle the result content and landing page*/
   document.getElementById("main").classList.toggle("hide");
   document.getElementById("launcher").classList.toggle("hide");
   document.getElementById("page-1").classList.toggle("hide");
   document.getElementById("landingTitle").classList.toggle("hide");
   
   /*get form data*/
   
   
   /*send data*/
   sendQuiz();
   
   /*receive results*/
   
   
   /*place reaults in correct spots*/
   placeResults();
    
    /*override twitter's tweet button styles to match our own button format?*/
   
});

function placeResults () {
   //placeholder results for testing purposes
   var songResult = "Back to December";
   var breakResult = "Back to December";
   var albumResult = "Taylor Swift";
   
   document.getElementById("your-song").innerHTML =songResult;
   document.getElementById("your-gen").innerHTML = albumResult;
   document.getElementById("your-breakup").innerHTML=breakResult;
   //document.getElementById("").innerHTML=
   
   /*construct twitter button content from results and include their script for generating a tweet*/
   var share= document.getElementById("shareBox");
   var shareContent = '<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://sonify.me"  data-text="I #Sonify '+ songResult +'! What'+'\'s your song? Find out at: \" data-hashtags=\"SonifyMe,TaylorSwift\" data-dnt=\"true\">Tweet</a>';
   //console.log(shareContent);
   share.innerHTML = shareContent;
   
   /**
   Twitter's tweet button and tweet generation script:
   */
   !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');
}

function sendQuiz(){
   //do the thing
   //do we want to return some kind of results object?  We can do that... 
   //and then in the placeResults function, we can extract each individual 
   //thing we need into separate variables rather than the placeholders we've got now
}


