<div class="col">
    <div class="table-responsive">
        <table class="table table-hover">
            <tr>
                <th>Name</th>
                <td>{{ $account->name }}</td>
            </tr>

            <tr>
                <th>ZohoID</th>
                <td>{{ $account->ZohoID }}</td>
            </tr>

            <tr>
                <th>Phone Number</th>
                <td>{{ $account->phone }}</td>
            </tr>

            <tr>
                <th>Street</th>
                <td>{{ $account->street }}</td>
            </tr>

            <tr>
                <th>City</th>
                <td>{{ $account->city }}</td>
            </tr>

            <tr>
                <th>State</th>
                <td>{{ $account->state }}</td>
            </tr>

            <tr>
                <th>Zipcode</th>
                <td>{{ $account->zipcode }}</td>
            </tr>

            <tr>
                <th>Country</th>
                <td>{{ $account->country }}</td>
            </tr>

            <tr>
                <th>Formatted Address</th>
                <td>{{ $account->formatted_address }}</td>
            </tr>

            <tr>
                <th>Formatted Address</th>
                <td><img src="{{static_map($account)}}" height="200" width="400" title="Current location of Account" /></td>
            </tr>

            <tr>
                <th>Website</th>
                <td>{{ $account->website }}</td>
            </tr>

            <tr>
                <th>Latitude</th>
                <td>{{ $account->lat }}</td>
            </tr>

            <tr>
                <th>Longitude</th>
                <td>{{ $account->lng }}</td>
            </tr>

            <tr>
                <th>Training Date</th>
                <td>{{ $account->training_date }}</td>
            </tr>

            <tr>
                <th>Tags</th>
                <td>{!! outputTags($account->tagNames(), true) !!}</td>
            </tr>

            <tr>
                <th>@lang('labels.backend.access.accounts.tabs.content.overview.status')</th>
                <td>@include('backend.accounts.includes.status', ['account' => $account])</td>
            </tr>

            <tr>
                <th>Override Sync</th>
                <td>@include('backend.accounts.includes.override', ['account' => $account])</td>
            </tr>

            <tr>
                <th>Created On</th>
                <td>{{ $account->created_at }}</td>
            </tr>
            <tr>
                <th>Modified On</th>
                <td>{{ $account->modified_at }}</td>
            </tr>
            <tr>
                <th>Synced On</th>
                <td>{{ $account->zoho_sync_date }}</td>
            </tr>
            <tr>
                <th>Zoho Created On</th>
                <td>{{ $account->zoho_created_at }}</td>
            </tr>
            <tr>
                <th>Zoho Modified On</th>
                <td>{{ $account->zoho_modified_at }}</td>
            </tr>

        </table>
    </div>
</div><!--table-responsive-->
