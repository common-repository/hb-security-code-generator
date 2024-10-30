window.addEventListener('load', (event) =>{

    var HBSAVE = document.getElementById('HBSAVE');
    if(HBSAVE){
        HBSAVE.addEventListener("submit", function(event) {

            event.preventDefault();

            var  hb_security_save = document.getElementById("hb-security-save");

            hb_security_save.disabled = true;


            var HBsecurityID    = document.getElementById('HBsecurityID').value,
                wpnonce         = document.getElementById('wpnonce').value,
                HBenable        = document.getElementById('HBenable').value;



            var formData = new FormData();
            formData.append( 'action', 'hb_save_securitycodeGenerator_action' );
            formData.append( 'HBsecurityID', HBsecurityID );
            formData.append( 'wpnonce', wpnonce );
            formData.append( 'HBenable', HBenable );

            (async () => {
                const rawResponse = await fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData
                })
                    .then( res => res.text() )
                    .then( function(data) {
                        if(data==0){
                            alert('Success');
                            location.reload();
                            hb_security_save.disabled = false;
                            return;
                        }

                        alert('Error');
                    })
                    .catch(function() {
                        alert('Error');
                    });

            })();


        });
    }



    var HBClose = document.getElementsByClassName('HBClose')

    if(HBClose){
        for(let i = 0; i < HBClose.length; i++) {
            HBClose[i].addEventListener("click", function(event) {

                event.preventDefault();

                var url_string      =   HBClose[i].getAttribute("href");
                var url_string      =   "https://piglet.me/"+url_string;
                var url             =   new URL(url_string);
                var HBCloseID     =   url.searchParams.get("HBCloseID");
                var wpnonce         =   url.searchParams.get("_wpnonce");

                var formData = new FormData();
                formData.append( 'action', 'hb_close_securitycodeGenerator_action' );
                formData.append( 'HBCloseID', HBCloseID );
                formData.append( 'wpnonce', wpnonce );


                (async () => {
                    const rawResponse = await fetch('/wp-admin/admin-ajax.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then( res => res.text() )
                        .then( function(data) {

                            console.log(data);
                            if(data==0){
                                alert('Success');
                                location.reload();
                                return;
                            }
                            //
                            // alert('Error');
                        })
                        .catch(function() {
                            alert('Error');
                        });

                })();









            });

        }
    }

    var HBcheckboxALL = document.getElementsByClassName('HBSECALL');

    if(HBcheckboxALL){
        var HBcheckbox = document.getElementsByName('hbsecurity[]'),
            HBhbTimeout = document.getElementsByName('hbTimeout[]');

        for(let i = 0; i < HBcheckboxALL.length; i++) {




            HBcheckboxALL[i].addEventListener("click", function(event) {

                for (var i = 0; i < HBcheckboxALL.length; i++) {

                    if(this.checked == true ){
                        if(HBcheckboxALL[i].checked==false)
                            HBcheckboxALL[i].checked = true;
                        }else{
                            HBcheckboxALL[i].checked = false;
                        }

                    }



                for (var i = 0; i < HBcheckbox.length; i++) {
                    if (HBcheckbox[i] != this)
                        HBcheckbox[i].checked = this.checked;
                }
                for (var i = 0; i < HBhbTimeout.length; i++) {
                    if (HBhbTimeout[i] != this)
                        HBhbTimeout[i].checked = this.checked;
                }
            });


        }
    }


    var VerificationCode = document.getElementById('VerificationCode');
    if(VerificationCode){
        VerificationCode.addEventListener("submit", function(event) {

            event.preventDefault();

            var  hbMake = document.getElementById("hbMake");

                hbMake.disabled = true;

            var  form = document.getElementById("VerificationCode").elements;

            var hb_length       = form[0].value,
                hb_prefix       = form[1].value,
                hb_rule         = form[2].value,
                hb_no           = form[3].value,
                hb_quantity     = form[4].value,
                hb_name         = form[5].value,
                hb_enable       = form[6].value,
                hb_deadline     = form[7].value;

            var formData = new FormData();
            formData.append( 'action', 'export_client_price_csv' );
            formData.append( 'hb_length', hb_length );
            formData.append( 'hb_prefix', hb_prefix );
            formData.append( 'hb_rule', hb_rule );
            formData.append( 'hb_no', hb_no );
            formData.append( 'hb_quantity', hb_quantity );
            formData.append( 'hb_name', hb_name );
            formData.append( 'hb_enable', hb_enable );
            formData.append( 'hb_deadline', hb_deadline );

            (async () => {
                const rawResponse = await fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData
                })
                    .then( res => res.text() )
                    .then( function(data) {

                        if(data!='200'){
                            var dataString = JSON.stringify(data);
                            var success = dataString.indexOf('\\n');
                            var code = dataString.substring(1,success);

                            data = data.slice(success)


                            var downloadLink = document.createElement("a");
                            var fileData = ['\ufeff'+data];

                            var blobObject = new Blob(fileData,{
                                type: "text/csv;charset=utf-8;"
                            });

                            var url = URL.createObjectURL(blobObject);
                            downloadLink.href = url;
                            downloadLink.download = "products.csv";

                            document.body.appendChild(downloadLink);
                            downloadLink.click();
                            document.body.removeChild(downloadLink);

                            hbMake.disabled = false;

                            alert('Success:'+ code);
                        }

                    })
                    .catch(function() {
                        alert('Error');
                    });

            })();







        });
    }

});

