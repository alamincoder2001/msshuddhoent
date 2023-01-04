<style>
	.v-select {
		margin-bottom: 5px;
	}

	.v-select.open .dropdown-toggle {
		border-bottom: 1px solid #ccc;
	}

	.v-select .dropdown-toggle {
		padding: 0px;
		height: 25px;
	}

	.v-select input[type=search],
	.v-select input[type=search]:focus {
		margin: 0px;
	}

	.v-select .vs__selected-options {
		overflow: hidden;
		flex-wrap: nowrap;
	}

	.v-select .selected-tag {
		margin: 2px 0px;
		white-space: nowrap;
		position: absolute;
		left: 0px;
	}

	.v-select .vs__actions {
		margin-top: -5px;
	}

	.v-select .dropdown-menu {
		width: auto;
		overflow-y: auto;
	}

	#customerPayment label {
		font-size: 13px;
	}

	#customerPayment select {
		border-radius: 3px;
		padding: 0;
	}

	#customerPayment .add-button {
		padding: 2.5px;
		width: 28px;
		background-color: #298db4;
		display: block;
		text-align: center;
		color: white;
	}

	#customerPayment .add-button:hover {
		background-color: #41add6;
		color: white;
	}
</style>
<div id="customerPayment">
	<div class="row" style="border-bottom: 1px solid #ccc;padding-bottom: 15px;margin-bottom: 15px;">
		<div class="col-md-12">
			<form @submit.prevent="saveCustomerPayment">
				<div class="row">
					<div class="col-md-5 col-md-offset-1">
						<div class="form-group">
							<label class="col-md-4 control-label">Payment Date</label>
							<label class="col-md-1">:</label>
							<div class="col-md-7">
								<input type="date" class="form-control" v-model="payment.date" required v-bind:disabled="userType == 'u' ? true : false">
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-4 control-label">Customer</label>
							<label class="col-md-1">:</label>
							<div class="col-md-6 col-xs-11">
								<v-select v-bind:options="customers" v-model="selectedCustomer" label="display_name" @input="getCustomerDue"></v-select>
							</div>
							<div class="col-md-1 col-xs-1" style="padding-left:0;margin-left:-3px">
								<a href="/customer" target="_blank" class="add-button"><i class="fa fa-plus"></i></a>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-4 control-label">Due</label>
							<label class="col-md-1">:</label>
							<div class="col-md-7">
								<input type="text" class="form-control" v-model="payment.previous_due" disabled>
							</div>
						</div>
					</div>
					<div class="col-md-5">
						<div class=" form-group">
							<label class="col-md-4 control-label">PO Charge Amount</label>
							<label class="col-md-1">:</label>
							<div class="col-md-7">
								<input type="number" class="form-control" v-model="payment.po_charge_amount" required>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-4 control-label">Description</label>
							<label class="col-md-1">:</label>
							<div class="col-md-7">
								<textarea class="form-control" v-model="payment.notes" cols="30" rows="2"></textarea>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-5 control-label"></label>
							<div class="col-md-7" style="text-align: right;">
								<input type="submit" class="btn btn-success btn-sm" value="Save">
								<!-- <input type="button" class="btn btn-danger btn-sm" value="Cancel" @click="resetForm"> -->
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>

	<div class="row">
		<div class="col-sm-12 form-inline">
			<div class="form-group">
				<label for="filter" class="sr-only">Filter</label>
				<input type="text" class="form-control" v-model="filter" placeholder="Filter">
			</div>
		</div>
		<div class="col-md-12">
			<div class="table-responsive">
				<datatable :columns="columns" :data="allCustomerPo" :filter-by="filter" style="margin-bottom: 5px;">
					<template scope="{ row }">
						<tr>
							<td>{{ row.date }}</td>
							<td>{{ row.Customer_Name }}</td>
							<td>{{ row.po_charge_amount }}</td>
							<td>{{ row.notes }}</td>
							<td>
								<?php if ($this->session->userdata('accountType') != 'u') { ?>
									<button type="button" class="button edit" @click="editPayment(row)">
										<i class="fa fa-pencil"></i>
									</button>
									<button type="button" class="button" @click="deletePayment(row.PO_id)">
										<i class="fa fa-trash"></i>
									</button>
								<?php } ?>
							</td>
						</tr>
					</template>
				</datatable>
				<datatable-pager v-model="page" type="abbreviated" :per-page="per_page" style="margin-bottom: 50px;"></datatable-pager>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url(); ?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/vuejs-datatable.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/vue-select.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/moment.min.js"></script>

<script>
	Vue.component('v-select', VueSelect.VueSelect);
	new Vue({
		el: '#customerPayment',
		data() {
			return {
				payment: {
					PO_id: '',
					customerID: '',
					date: moment().format('YYYY-MM-DD'),
					previous_due: 0,
					po_charge_amount: 0,
					notes: '',
				},
				allCustomerPo: [],
				customers: [],
				selectedCustomer: {
					display_name: 'Select Customer',
					Customer_Name: ''
				},
				userType: '<?php echo $this->session->userdata("accountType"); ?>',

				columns: [{
						label: 'Date',
						field: 'date',
						align: 'center'
					},
					{
						label: 'Customer',
						field: 'Customer_Name',
						align: 'center'
					},
					{
						label: 'PO Charge Amount',
						field: 'po_charge_amount',
						align: 'center'
					},
					{
						label: 'Description',
						field: 'notes',
						align: 'center'
					},
					{
						label: 'Action',
						align: 'center',
						filterable: false
					}
				],
				page: 1,
				per_page: 10,
				filter: ''
			}
		},
		computed: {

		},
		created() {
			this.getCustomers();
			this.getCustomerPO();
		},
		methods: {
			getCustomers() {
				axios.get('/get_customers').then(res => {
					this.customers = res.data;
				})
			},
			getCustomerPO() {
				axios.get('/get_customer_po').then(res => {
					this.allCustomerPo = res.data;
				})
			},
			getCustomerDue() {
				if (this.selectedCustomer == null || this.selectedCustomer.Customer_SlNo == undefined) {
					return;
				}

				axios.post('/get_customer_due', {
					customerId: this.selectedCustomer.Customer_SlNo
				}).then(res => {
					this.payment.previous_due = res.data[0].dueAmount;
				})
			},
			saveCustomerPayment() {

				if (this.selectedCustomer == null || this.selectedCustomer.Customer_SlNo == undefined) {
					alert('Select Customer');
					return;
				}

				this.payment.customerID = this.selectedCustomer.Customer_SlNo;

				let url = '/save_customer_po';
				if (this.payment.PO_id != '') {
					url = '/update_customer_po';
				}
				axios.post(url, this.payment).then(res => {
					let r = res.data;
					alert(r.message);
					if (r.success) {
						this.resetForm();
						this.getCustomerPO();
						// let invoiceConfirm = confirm('Do you want to view invoice?');
						// if (invoiceConfirm == true) {
						// 	window.open('/paymentAndReport/' + r.paymentId, '_blank');
						// }
					}
				})
			},
			editPayment(payment) {
				let keys = Object.keys(this.payment);
				keys.forEach(key => {
					this.payment[key] = payment[key];
				})

				this.selectedCustomer = {
					Customer_SlNo: payment.customerID,
					Customer_Name: payment.Customer_Name,
					display_name: `${payment.Customer_Code} - ${payment.Customer_Name}`
				}
			},
			deletePayment(paymentId) {
				let deleteConfirm = confirm('Are you sure?');
				if (deleteConfirm == false) {
					return;
				}
				axios.post('/delete_customer_po', {
					POId: paymentId
				}).then(res => {
					let r = res.data;
					alert(r.message);
					if (r.success) {
						this.getCustomerPO();
					}
				})
			},
			resetForm() {
				this.payment.PO_id = '';
				this.payment.customerID = '';
				this.payment.previous_due = 0;
				this.payment.po_charge_amount = '';
				this.payment.notes = '';
				this.payment.date = moment().format('YYYY-MM-DD');

				this.selectedCustomer = {
					display_name: 'Select Customer',
					Customer_Name: ''
				}

			}
		}
	})
</script>