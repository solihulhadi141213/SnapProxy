<!-- Aplication -->
<div class="accordion-item">
    <h2 class="accordion-header" id="headingAplication">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAplication" aria-expanded="false" aria-controls="collapseAplication">
            <b>Aplication</b>
        </button>
    </h2>
    <div id="collapseAplication" class="accordion-collapse collapse" aria-labelledby="headingAplication" data-bs-parent="#accordionDocs">
        <div class="accordion-body">
            <?php
                //Routing Akses
                if(empty($id_account_session)){
                    include "_Page/Login/Login.php";
                }else{
                    
                }
            ?>
        </div>
    </div>
</div>