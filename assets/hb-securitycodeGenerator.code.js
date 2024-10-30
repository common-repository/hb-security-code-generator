window.onload = function() {
    var mycanvas=document.getElementById('mycanvas');

    if(!mycanvas) return;
    var cxt=mycanvas.getContext('2d');
    var validate="";
    var baseColor=["#212F3D"];
    var sColor=["#B22222"];
    var fColor=["#F7F9F9"];
    var indexColor="";


    function randColor(){
        indexColor="";
        indexColor=Math.floor(Math.random()*baseColor.length); //亂數取得 0~顏色陣列長度
        return  indexColor;
    }


    function rand(){
        validate="";

        var str="0123456789ABCDEFGHJKLMNPQRSTUVWXYZ";
        var arr=str.split("");
        var ranNum;
        for(var i=0;i<6;i++){
            ranNum=Math.floor(Math.random()*33);   //隨機數在[0,65]之間
            validate+=arr[ranNum];
        }

        return validate;
    }


    function lineX(){
        var ranLineX=Math.floor(Math.random()*150);
        return ranLineX;
    }


    function lineY(){
        var ranLineY=Math.floor(Math.random()*40);
        return ranLineY;
    }


    function clickChange(){
        var i=randColor();

        cxt.beginPath();
        cxt.fillStyle=baseColor[i];
        cxt.fillRect(0,0,150,40);


        for(var j=0;j<40;j++){


            cxt.beginPath();
            cxt.fillStyle = sColor[i];
            var arcSize=(Math.floor(Math.random()*(50-5+1))+5)/10;
            cxt.arc(lineX(), lineY(), arcSize, 0, 2 * Math.PI);
            cxt.fill();

        }
        cxt.fillStyle=fColor[i];
        cxt.font='bold 25px Verdana';
        cxt.fillText(rand(),10,30);
    }



    mycanvas.onclick=function(e){
        e.preventDefault();
        clickChange();
    }


    var myform=document.getElementById('myform');

    var formData = new FormData();
    formData.append( 'action', 'hb_Verification_code_action' );

    myform.addEventListener("submit",function(e){

        var vad = myform.hbcode.value;


        if(vad.toUpperCase()===validate.toUpperCase()){

            var SecurityCode = myform.SecurityCode.value;

            if(!SecurityCode){
                e.preventDefault();
            }

        }
        else{
            e.preventDefault();
            alert("Please Confirm !");

        }


    });



    clickChange();

}