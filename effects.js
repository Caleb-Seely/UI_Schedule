function checkInput(){

return true;
}

function hide_new_db(){
   document.getElementById('newdb').style.display = 'none';
}

function show_new_db(){
   document.getElementById('newdb').style.display = 'block';
}

function show_sqlodbs(){
   document.getElementById('sqlodbs').style.display = 'block';
   document.getElementById('sqldbs').style.display = 'none';
   document.getElementById('blank').style.display = 'none';
}

function show_sqldbs(){
   document.getElementById('sqlodbs').style.display = 'none';
   document.getElementById('sqldbs').style.display = 'block';
   document.getElementById('blank').style.display = 'none';  
}

function show_table_list(){
   document.getElementById('tbl_list').style.display = 'block';

   var e = document.getElementById("sqldblist");
   console.log(e);
   //var value = e.options[e.selectedIndex].value;
   var text = e.options[e.selectedIndex].text;
   console.log(text);
}

var coll = document.getElementsByClassName("collapsible");
var i;

for(i = 0; i < coll.length; i++){
   coll[i].addEventListener("click", function(){
      this.classList.toggle("active");
      var content = this.nextElementSibling;
      if(content.style.makHeight){
         content.style.makHeight = NULL;
      }
      else{
         content.style.maxHeight = content.scrollHeight + "px";
      }
   });
}

