/**
 * serverCtl js common utils library
 */

function toggleCollectionByClass(className)
{
    var col = $('.'+className);
    $.each(col, function(id, value)
                            {
                                console.log(id+" "+value);
                                $(value).toggle();
                            });
}
