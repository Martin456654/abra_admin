"use strict";

// start page
const futurePosts = document.getElementById("futurePostsOnlyPost");
futurePosts.style.display = "none";

const pastPosts = document.getElementById("pastPostsOnlyPost");
pastPosts.style.display = "none";

// future posts
const futureBtn = document.getElementById("futurePostsBtn");

let futureBtnHidden = true;
futureBtn.addEventListener("click", () => {
    if(futureBtnHidden == false){
        futurePosts.style.display = "none";
        futureBtn.innerHTML = "Zobrazit budoucí příspěvky&nbsp;&nbsp;<i class='fas fa-arrow-down'></i>";
        futureBtnHidden = true;
    }else{
        futurePosts.style.display = "block";
        futureBtn.innerHTML = "Skrýt budoucí příspěvky&nbsp;&nbsp;<i class='fas fa-times'></i>";
        futureBtnHidden = false;
    }
})


// past posts
const pastBtn = document.getElementById("pastPostsBtn");

let pastBtnHidden = true;
pastBtn.addEventListener("click", function togglePastPosts(){
    if(pastBtnHidden == false){
        pastPosts.style.display = "none";

        pastBtn.innerHTML = "Zobrazit příspěvky z historie&nbsp;&nbsp;<i class='fas fa-arrow-down'></i>";
        pastBtnHidden = true;
    }else{
        pastPosts.style.display = "block";
        
        pastBtn.innerHTML = "Skrýt příspěvky z historie&nbsp;&nbsp;<i class='fas fa-times'></i>";
        pastBtnHidden = false;
    }
})