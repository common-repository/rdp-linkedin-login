var $j=jQuery.noConflict();
// Use jQuery via $j(...)

function rdp_ll_openPopupCenter(pageURL, title, w, h) {
    var left = (screen.width - w) / 2;
    var top = (screen.height - h) / 4;  // for 25% - divide by 4  |  for 33% - divide by 3
    var targetWin = window.open(pageURL, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
} 

function rdp_ll_get_source_element(e){
    if( !e ) e = window.event;
    var target;
    if(e.target||e.srcElement){
        target = e.target||e.srcElement;
    }else target = e;  
    return target;    
}//rdp_ll_get_source_element


