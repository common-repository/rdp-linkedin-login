var $j=jQuery.noConflict();
// Use jQuery via $j(...)


$j(document).ready(rdp_ll_login_onReady);

function rdp_ll_login_onReady(){
    $j('body').on( "click", '.btnRDPLLogin' , rdp_ll_login_showPopup ); 
  
    
    $j(".rdp-ll-loginout.logged-in-true").on('click', function() {
        var oMenu = $j("#rdp-ll-sub-wrapper");
        if(oMenu.hasClass('hidden')){
            oMenu.addClass('visible').removeClass('hidden');
            var pos = $j.PositionCalculator( {
                target: this,
                targetAt: "bottom right",
                item: oMenu,
                itemAt: "top right",
                flip: "both"
            }).calculate();

            oMenu.css({
                top: parseInt(oMenu.css('top')) + pos.moveBy.y + "px",
                left: parseInt(oMenu.css('left')) + pos.moveBy.x + "px"
            });        
        }else{
            oMenu.addClass('hidden').removeClass('visible');
        }
    });
    
    $j("#rdp-ll-sub-wrapper").on('mouseleave',function(){$j(this).addClass('hidden').removeClass('visible');})    
}


function rdp_ll_login_showPopup(e){
    var sURL = rdp_ll_login.loginurl;
    var queryObject = jQuery.query.load(rdp_ll_login.loginurl);
    queryObject.SET('rdpllaction','login');
    
    var cb = 'id-' + Math.random().toString(36).substr(2, 16);
    queryObject.SET('cb',cb);

    var params = queryObject.toString();
    sURL += params;
    console.log(sURL);

    rdp_ll_openPopupCenter(sURL, '', 400, 600);
}



