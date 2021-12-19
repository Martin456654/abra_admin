"use st"

const changePassBtn = document.getElementById("changePassBtn");
const changePassWrap = document.getElementById("changePassWrap");

changePassWrap.style.display = "none";

let btnTrigger = false;
changePassBtn.addEventListener("click", () => {
    if(btnTrigger == false){

        changePassWrap.style.display = "block";
        btnTrigger = true;

    }else if(btnTrigger == true){
        changePassWrap.style.display = "none";
        btnTrigger = false;
    }
})