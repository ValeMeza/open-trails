<div class="model-body">
	<div class="container">
		<form id="trackModal" name="trackModel" class="form-inline">
			<h2> Let Us Track Your Progress</h2>
			<button class="btn btn-info" ng-click="geoFindMe();">Begin Tracking</button>
			<h2>Save Your Progress</h2>
			<button class="btn btn-info" ng-click="ok();">Save Trail</button>
			<button class="btn btn-danger" ng-click="cancel();">Cancel</button>
		</form>
		<p>
			{{points}}
		</p>
	</div>
</div>