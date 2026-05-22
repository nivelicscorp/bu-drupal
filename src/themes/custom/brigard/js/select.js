
jQuery(document).ready(function() {
  console.log(drupalSettings.path.currentLanguage)
  
  if(jQuery("#views-exposed-form-lawyers-search-page").length > 0){
      if (drupalSettings.path.currentLanguage == 'en'){
          jQuery('[name="field_ciudad_target_id"] option:contains("Londres")').text('London');
          jQuery('[name="field_ciudad_target_id"] option:contains("Singapur")').text('Singapore');
  
      }
  }
})

var x, i, j, selElmnt, a, b, c, value_select;
x = document.getElementsByClassName("select-wrapper");

for (i = 0; i < x.length; i++) {

  selElmnt = x[i].getElementsByTagName("select")[0];  
 
  a = document.createElement("DIV");
  a.setAttribute("class", "select-selected");
  a.innerHTML = selElmnt.options[selElmnt.selectedIndex].innerHTML;
  x[i].appendChild(a);
  
  b = document.createElement("DIV");
  b.setAttribute("class", "select-items select-hide"); 
  for (j = 0; j < selElmnt.length; j++) {
    c = document.createElement("DIV");
    
    var valurOpt = selElmnt.options[j].value;
		  
    if (drupalSettings.path.currentLanguage === "en" && valurOpt.toString() === "1420"){
			c.innerHTML = "London";
			console.log(selElmnt.options[j].innerHTML);
		}
		else if (drupalSettings.path.currentLanguage === "en" && valurOpt.toString() === "1421"){
			c.innerHTML = "Singapore";
			console.log(selElmnt.options[j].innerHTML);
		}
    else {
      c.innerHTML = selElmnt.options[j].innerHTML;
    }
    value_select = selElmnt.options[j].value;
    c.setAttribute('value',value_select);
    c.addEventListener("click", function(e) {
        var y, i, k, s, h;
        s = this.parentNode.parentNode.getElementsByTagName("select")[0];
        h = this.parentNode.previousSibling;
        for (i = 0; i < s.length; i++) {
          if (s.options[i].innerHTML == this.innerHTML) {
            s.selectedIndex = i;
            h.innerHTML = this.innerHTML;
            y = this.parentNode.getElementsByClassName("same-as-selected");
            for (k = 0; k < y.length; k++) {
              y[k].removeAttribute("class");
            }
            this.setAttribute("class", "same-as-selected");
            this.parentNode.parentNode.getElementsByTagName("div")[0].setAttribute("class", "select-selected-active select-selected");
            break;
          }
        }
        h.click();
    });
    b.appendChild(c);
  }
  x[i].appendChild(b);
  a.addEventListener("click", function(e) {
      e.stopPropagation();
      closeAllSelect(this);
      this.nextSibling.classList.toggle("select-hide");
      this.classList.toggle("select-arrow-active");
    });
}
function closeAllSelect(elmnt) {
  var x, y, i, arrNo = [];
  x = document.getElementsByClassName("select-items");
  y = document.getElementsByClassName("select-selected");
  for (i = 0; i < y.length; i++) {
    if (elmnt == y[i]) {
      arrNo.push(i)
    } else {
      y[i].classList.remove("select-arrow-active");
    }
  }
  for (i = 0; i < x.length; i++) {
    if (arrNo.indexOf(i)) {
      x[i].classList.add("select-hide");
    }
  }
}

document.addEventListener("click", closeAllSelect);