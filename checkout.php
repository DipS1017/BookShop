<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://khalti.s3.ap-south-1.amazonaws.com/KPG/dist/2020.12.17.0.0.0/khalti-checkout.iffe.js"></script>


<?php
$total = 0;
$qry = $conn->query("SELECT c.*,p.title,i.price,p.id as pid from `cart` c inner join `inventory` i on i.id=c.inventory_id inner join products p on p.id = i.product_id where c.client_id = ".$_settings->userdata('id'));
while($row = $qry->fetch_assoc()):
    $total += $row['price'] * $row['quantity'];
endwhile;
?>

<section class="py-5">
    <div class="container">
        <div class="card rounded-0">
            <div class="card-body">
                <h3 class="text-center"><b>Checkout</b></h3>
                <hr class="border-dark">
                <form action="" id="place_order">
                    <input type="hidden" name="amount" value="<?php echo $total ?>">
                    <input type="hidden" name="payment_method" value="Online Payment">
                    <input type="hidden" name="paid" value="0">
                    <!-- Add a hidden input field to store the payment token -->
                    <input type="hidden" name="payment_token" id="payment_token" value="">
                    <div class="row row-col-1 justify-content-center">
                        <div class="col-6">
                            <div class="form-group col mb-0">
                                <label for="" class="control-label">Order Type</label>
                            </div>
                            <div class="form-group d-flex pl-2">
                                <div class="custom-control custom-radio">
                                    <input class="custom-control-input custom-control-input-primary" type="radio" id="customRadio4" name="order_type" value="2" checked="">
                                    <label for="customRadio4" class="custom-control-label">For Delivery</label>
                                </div>
                                <div class="custom-control custom-radio ml-3">
                                    <input class="custom-control-input custom-control-input-primary custom-control-input-outline" type="radio" id="customRadio5" name="order_type" value="1">
                                    <label for="customRadio5" class="custom-control-label">For Pick up</label>
                                </div>
                            </div>
                            <div class="form-group col address-holder">
                                <label for="" class="control-label">Delivery Address</label>
                                <textarea id="" cols="30" rows="3" name="delivery_address" class="form-control" style="resize:none"><?php echo $_settings->userdata('default_delivery_address') ?></textarea>
                            </div>
                            <div class="col">
                                <span><h4><b>Total:</b> <?php echo number_format($total) ?></h4></span>
                            </div>
                            <hr>
                            <div class="col my-3">
                                <h4 class="text-muted">Payment Method</h4>
                                <div class="d-flex w-100 justify-content-between">
                                    <button class="btn btn-flat btn-success" id="khalti-button">Pay with Khalti</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
   var config = {
    "publicKey": "test_public_key_4eb78b46ce784eec8d6638a13eb53551",
    "productIdentity": "1234567890",
    "productName": "Product Name",
    "productUrl": "http://example.com/product/url",
    "paymentPreference": ["KHALTI", "EBANKING", "MOBILE_BANKING", "CONNECT_IPS", "SCT"],
    "eventHandler": {
        onSuccess(payload) {
    console.log("Khalti Payment Success. Payload:", payload);
    var paymentToken = payload.token;

    console.log("Payment Token:", paymentToken);
    if (paymentToken) {
        $('#payment_token').val(paymentToken);
        payment_online(paymentToken);
    } else {
        console.log("Payment Token is empty or undefined.");
    }
},

        onError(error) {
            console.log(error);
        },
        onClose() {
            console.log('widget is closing');
        }
    }
};


    var checkout = new KhaltiCheckout(config);

    document.getElementById("khalti-button").onclick = function (event) {
    event.preventDefault();
    checkout.show({ amount: <?php echo $total; ?> * 100 });
};

function payment_online(paymentToken) {
    console.log("Payment Token for AJAX Request:", paymentToken);

    // Set payment method to "Online Payment" and paid to 1
    $('[name="payment_method"]').val("Online Payment");
    $('[name="paid"]').val(1);

    // Set the payment token in the hidden field
    $('#payment_token').val(paymentToken);

    // Use AJAX to submit the form data
    $.ajax({
        url: 'classes/Master.php?f=place_order',
        method: 'POST',
        data: $('#place_order').serialize(),
        dataType: "json",
        error: function (err) {
            console.log(err);
            alert_toast("An error occurred", "error");
            end_loader(); // Add this line
        },
        success: function (resp) {
            if (!!resp.status && resp.status == 'success') {
                alert_toast("Order Successfully placed.", "success");
            } else {
                console.log(resp);
                alert_toast("An error occurred", "error");
            }
            end_loader(); // Add this line
        }
    });
}



$(function () {
    $('[name="order_type"]').change(function () {
        if ($(this).val() == 2) {
            $('.address-holder').hide('slow');
        } else {
            $('.address-holder').show('slow');
        }
    });
});

</script>
