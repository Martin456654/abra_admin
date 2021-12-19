// variables - elements
const addNewBtn = document.getElementById("newSbmtBtn");
const postName = document.getElementById("postName");
const postContent = document.getElementById("postContent");

let change = false;

// start
addNewBtn.classList.add("nonclickable");

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
    if(postName.value != "" && postContent.value != ""){
        addNewBtn.classList.remove("nonclickable");
    }else{
        addNewBtn.classList.add("nonclickable");
    }
}