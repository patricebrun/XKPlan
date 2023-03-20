$(function () {

    // $('#myModalSaison').on('show.bs.modal', function () {
    //     $(this).find('.modal-saison-body').css({
    //         'max-height':'100%'
    //     });
    // });

    $('.add-periode').click(function(){
       $('#saison_id').val($(this).data('idsaison'));
       $('#codeSaison').val($(this).data('codesaison'));
       $('#nom-PeriodeSaison').val($(this).data('codesaison')+" : ");
    });

    $('#select-saison,input[name=saison]').on('change',function (e) {
        var annee = $("#select-saison").val();
        var saison = $('input[name=saison]:checked').val();

        if("" == annee){
            $("#code-saison").val("");
            $("#date-debut-saison").val("");
            $("#date-fin-saison").val("");

            return;
        }

        $.ajax({
            type: 'get',
            url: Routing.generate('saison_dates_iata', { annee : annee, saison: saison}),
            success: function (data) {
                $("#code-saison").val(data.code);
                $("#date-debut-saison").val(data.dateDebut);
                $("#date-fin-saison").val(data.dateFin);
            }
        });

    });

    $('.saisonVisible').click(function (){
        var idSaison = $(this).data('id');
        var visible;
        var eltSaison = $(this);
        if($(this).is(":checked")){
            visible = 1;
        }else{
            visible = 0;
        }
        $.ajax({
            url: Routing.generate('saison_setsaisonvisible'),
            dataType: 'json',
            type: 'POST',
            data: {
                id: idSaison, //journalier ou hebdomadaire
                visible: visible,
            },
            success: function (data) {
                if(!data.valide){
                    alert("erreur");
                }else{
                    // $('#myModal-saison-'+idSaison).modal('toggle');
                    // $('.modal-body').html("<span class='text-success'>Mise à jour effectuée avec succés</span>");
                }
            }
        });
    })

    $('.periodeSaisonVisible').click(function (){
        var idPeriodeSaison = $(this).data('id');
        var visible;
        if($(this).is(":checked")){
            visible = 1;
        }else{
            visible = 0;
        }
        var eltPeriodeSaison = $(this);
        $.ajax({
            url: Routing.generate('saison_setperiodesaisonvisible'),
            dataType: 'json',
            type: 'POST',
            data: {
                id: idPeriodeSaison,
                visible: visible,
            },
            success: function (data) {
                if(!data.valide){
                    alert("erreur");
                }else{
                    if($(eltPeriodeSaison).parent().parent().parent().parent().parent().find('input:checkbox:checked').length == 0){
                        var idSaison = $(eltPeriodeSaison).parent().parent().parent().parent().parent().parent().parent().parent().find('input.saisonVisible').data('id');
                        $(eltPeriodeSaison).parent().parent().parent().parent().parent().parent().parent().parent().find('input.saisonVisible').attr('checked',false);
                        $.ajax({
                            url: Routing.generate('saison_setsaisonvisible'),
                            dataType: 'json',
                            type: 'POST',
                            data: {
                                id: idSaison,
                                visible: 0,
                            },
                            success: function (data) {
                                if(!data.valide){
                                }
                            }
                        });
                    }
                    $('#myModal-'+idPeriodeSaison).modal('toggle');
                    // $('.modal-body').html("<span class='text-success'>Mise à jour effectuée avec succés</span>");
                }
            }
        });
    })
});
