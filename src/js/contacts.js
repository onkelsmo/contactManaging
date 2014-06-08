/* 
 * @copyright jsmolka
 * @link https://github.com/onkelsmo/contactManaging
 */
$(function() {
    function listContacts(data) {
        var contacts = $("<ul>");
        for(var id in data.datas) {
            var contact = $("<li>");
            contacts.append(contact);
            contact.append(
                    showContact(id, data.datas[id]));
        }
        $("#contacts").append(contacts);
    }
    
    $.getJSON('../php/ContactManager/Services/Contacts.php', listContacts);
    
    function showContact(id, contact) {
        var dl = $("<dl>");
        for(var i in contact) {
            var inserts = contact[i];
            dl.append($("<dt>").text(inserts.name + ":"));
            if(inserts.insert instanceof Array) {
                for(var j in inserts.insert) {
                    dl.append($("<dd>").append(
                            $("<input>")
                            .attr('name', 'bearbeitet[' + inserts.insert[j].id + ']')
                            .val(inserts.insert[j].value)));
                }
                dl.append($("<dd>").append(
                        $("<input name='neu[" + id + "]["
                        + inserts.id + "]["
                        + showContact.newInsert++ + "]' >")));
            } else {
                dl.append($("<dd>").append(
                        $("<input>").attr('name', 'bearbeitet[' + inserts.insert.id + ']').val(inserts.insert.value)));
            }
        }
        return dl;
    };
    showContact.newInsert = 0;
});

