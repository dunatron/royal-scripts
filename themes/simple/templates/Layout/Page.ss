<% include SideBar %>
<div class="content-container unit size3of4 lastUnit">
	<article>
		<h1>$Title</h1>
		<div class="content">$Content</div>
		<%-- Event Finda --%>
		<%--
        <% control $getEventFindaEvents %>
			<% loop $Events %>
				<h1>$Title</h1>
				<h2>$UserName</h2>
				<p>$Description</p>
			<% end_loop %>
        <% end_control %>
        --%>

		<%-- Event Brite--%>
		<%--
		<% control $getEventBriteEvents %>
			<% loop $Events %>
                <h1>$Title</h1>
                <h2>$UserName</h2>
                <p>$Description</p>
			<% end_loop %>
		<% end_control %>
		--%>

		<%--<% control $getAllEvents %>--%>
			<%--<% loop $Events %>--%>
                <%--<h1>Title: $Title <span>$ID</span></h1>--%>
                <%--<h2>Username: $UserName</h2>--%>
                <%--<p>Description: $Description</p>--%>
				<%--<p>StartDate: $StartDate</p>--%>
				<%--<p>EndDate: $EndDate</p>--%>
				<%--<p>Address: Address: $Address</p>--%>
				<%--<p>Venue: $Venue</p>--%>
				<%--<p>IsLive: $IsLive</p>--%>
				<%--<p>Username: $UserName</p>--%>
				<%--<p>Phone: $Phone</p>--%>
				<%--<p>URL: $EventURL</p>--%>
				<%--<p>Ticket Website: $TicketWebsite</p>--%>
				<%--<p>Capacity: $Capacity</p>--%>
				<%--<img src="$ExternalImageURL">--%>
				<%--<p>Topic: $EventTopic</p>--%>
			<%--<% end_loop %>--%>
		<%--<% end_control %>--%>

	</article>
		$Form
		$CommentsForm

	<%-- Get the Mail Chimp Lists --%>
	<% control $getMailChimpLists %>
		<h1>Mail Chimp Lists</h1>
		<% loop $Lists %>
            <p>===========================</p>
			<p>ID: $id</p>
            <p>name: $name</p>
		<% end_loop %>
	<% end_control %>

	<%-- Create MailChimp Campaign --%>
    $createMailChimpCampaign

	<%-- Create MailChimp Content --%>
    $createMailChimpContent

	<%-- Get the Mail Chimp Templates --%>
	<% control $getMailChimpTemplates %>
        <h1>Mail Chimp Templates</h1>
		<% loop $Templates %>
			<p>===========================</p>
            <p>ID: $id</p>
            <p>name: $name</p>
            <p>type: $type</p>
			<img src="$thumbnail">
		<% end_loop %>
	<% end_control %>

</div>