<% loop $getMailChimpLists %>
    <h1>$id : $name</h1>
<% end_loop %>


<% loop $getListMembers('ca425d5957') %>
    <h1>$id : $email_address</h1>
<% end_loop %>
$addMemberToMailChimpList('ca425d5957','Jerome.roberts@samdog.nz','jerome','Roberts')



<%--$getListMembers--%>