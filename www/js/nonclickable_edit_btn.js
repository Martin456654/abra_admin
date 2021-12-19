"use strict";

const editBtn = document.getElementById("editBtn");

const postName = document.getElementById("postName")
const postContent = document.getElementById("postContent");
const postDate = document.getElementById("postDate");

const defName = postName.value;
const defContent = postContent.value;
const defDate = postDate.value;

// start
editBtn.classList.add("nonclickable");

// add multiple events in addEventListener()
function addListenerMulti(element, eventNames, listener) {
    var events = eventNames.split(' ');
    for (var i=0, iLen=events.length; i<iLen; i++) {
      element.addEventListener(events[i], listener, false);
    }
}

// do after click or key event
addListenerMulti(window, 'click keydown keyup change input', changeActiontionOfBtn);

// actualize changes every half second -> nefunguje addEventListener na CKEditor 4
setInterval(changeActiontionOfBtn, 500);

// change activation of button
function changeActiontionOfBtn(){
    let actualName = postName.value;
    let actualContent = postContent.value;
    let actualDate =  postDate.value;

    if(actualName != defName || actualContent != defContent || actualDate != defDate){
    // if(actualName != defName){
        editBtn.classList.remove("nonclickable");
    }else{
        editBtn.classList.add("nonclickable");
    }
}