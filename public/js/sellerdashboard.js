
$(document).ready(function() {
    // Function to load order details
    function loadOrderDetails(orderId) {
        showLoading();

        $.ajax({
            url: '/seller/orders/' + orderId,
            type: 'GET',
            success: function(response) {
                hideLoading();

                if (response.success && response.order) {
                    var order = response.order;
                    var itemsHtml = '';

                    if (order.order_items && order.order_items.length > 0) {
                        order.order_items.forEach(function(item, index) {
                            itemsHtml += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${item.product ? item.product.name : 'Unknown Product'}</td>
                                    <td>${item.quantity || 0}</td>
                                    <td>Tsh ${parseFloat(item.price || 0).toFixed(0)}</td>
                                    <td>Tsh ${parseFloat((item.price || 0) * (item.quantity || 0)).toFixed(0)}</td>
                                </tr>
                            `;
                        });
                    }

                    var shippingInfo = order.order_address ? `
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Shipping Address</h6>
                                <address>
                                    ${order.order_address.first_name || ''} ${order.order_address.last_name || ''}<br>
                                    ${order.order_address.address || ''}<br>
                                    ${order.order_address.city || ''}, ${order.order_address.state || ''} ${order.order_address.zip_code || ''}<br>
                                    ${order.order_address.country || ''}<br>
                                    Phone: ${order.order_address.phone || ''}
                                </address>
                            </div>
                            <div class="col-md-6">
                                <h6>Order Summary</h6>
                                <p><strong>Subtotal:</strong> Tsh ${parseFloat(order.subtotal || 0).toFixed(0)}</p>
                                <p><strong>Total:</strong> Tsh ${parseFloat(order.total_amount || 0).toFixed(0)}</p>
                            </div>
                        </div>
                    ` : '<p>No shipping information available</p>';

                    var content = `
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">Order Information</h6>
                                        <p><strong>Order Number:</strong> <span class="font-monospace">${order.order_number || order.public_id || 'N/A'}</span></p>
                                        <p><strong>Customer:</strong> ${order.user ? order.user.name : 'Unknown'}</p>
                                        <p><strong>Email:</strong> ${order.user ? order.user.email : 'N/A'}</p>
                                        <p><strong>Phone:</strong> ${order.user ? (order.user.phone || 'N/A') : 'N/A'}</p>
                                        <p><strong>Status:</strong> <span class="order-status-badge status-${order.status}">${order.status ? order.status.charAt(0).toUpperCase() + order.status.slice(1) : 'Unknown'}</span></p>
                                        <p><strong>Payment Status:</strong> <span class="badge bg-${order.payment_status == 'paid' ? 'success' : 'warning'}">${order.payment_status ? order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1) : 'Unknown'}</span></p>
                                        <p><strong>Order Date:</strong> ${order.created_at ? new Date(order.created_at).toLocaleString() : 'Unknown'}</p>
                                        <p><strong>Notes:</strong> ${order.notes || 'No notes'}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">Order Items</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Product</th>
                                                        <th>Qty</th>
                                                        <th>Price</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${itemsHtml}
                                                    <tr class="table-active">
                                                        <td colspan="4"><strong>Order Total</strong></td>
                                                        <td><strong>Tsh ${parseFloat(order.total_amount || 0).toFixed(0)}</strong></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        ${shippingInfo}
                    `;

                    $('#orderDetailsContent').html(content);
                    $('#viewOrderModal').modal('show');
                } else {
                    $('#orderDetailsContent').html('<div class="alert alert-danger">Order data not found</div>');
                    $('#viewOrderModal').modal('show');
                }
            },
            error: function(xhr) {
                hideLoading();
                var errorMsg = xhr.responseJSON?.message || 'Failed to load order details';
                $('#orderDetailsContent').html('<div class="alert alert-danger">' + errorMsg + '</div>');
                $('#viewOrderModal').modal('show');
            }
        });
    }

    // Show loading overlay
    function showLoading() {
        if (!$('#loadingOverlay').length) {
            $('body').append(`
                <div id="loadingOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); display: flex; justify-content: center; align-items: center; z-index: 1050;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);
        }
        $('#loadingOverlay').css('display', 'flex');
    }

    // Hide loading overlay
    function hideLoading() {
        $('#loadingOverlay').hide();
    }

    // View Order button click handler
    $('.view-order-btn').click(function(e) {
        e.preventDefault();
        var orderId = $(this).data('order-id');
        if (orderId) {
            loadOrderDetails(orderId);
        }
    });

    // Function to load product data
    function loadProductData(productId) {
        $.ajax({
            url: '/seller/products/' + productId,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    var product = response.product;

                    // Set view data
                    $('#viewName').text(product.name);
                    $('#viewCategory').text(product.category?.name || 'N/A');

                    // Set thumbnail
                    if (product.thumbnail) {
                        $('#viewThumbnail').attr('src', '/storage/' + product.thumbnail);
                    } else {
                        $('#viewThumbnail').attr('src', '/img/logo.png');
                    }

                    // Set price
                    var priceHtml = 'Tsh ' + parseFloat(product.new_price).toFixed(0);
                    if (product.old_price) {
                        priceHtml += ' <small class="text-decoration-line-through text-muted">Tsh ' + parseFloat(product.old_price).toFixed(0) + '</small>';
                        $('#viewOldPrice').text('Tsh ' + parseFloat(product.old_price).toFixed(0)).show();
                    } else {
                        $('#viewOldPrice').hide();
                    }
                    $('#viewPrice').html(priceHtml);

                    // Set stock
                    $('#viewStock').text('Stock: ' + product.stock);

                    // Set rating
                    var ratingHtml = '';
                    for (var i = 1; i <= 5; i++) {
                        if (i <= product.rate) {
                            ratingHtml += '<i class="fas fa-star text-warning"></i>';
                        } else {
                            ratingHtml += '<i class="far fa-star text-muted"></i>';
                        }
                    }
                    $('#viewRating').html(ratingHtml);

                    // Set description data
                    if (product.description) {
                        $('#viewDescription').text(product.description.description || 'No description available');
                        $('#viewSpecifications').text(product.description.specifications || 'No specifications available');
                        $('#viewDetails').text(product.description.details || 'No details available');
                    } else {
                        $('#viewDescription').text('No description available');
                        $('#viewSpecifications').text('No specifications available');
                        $('#viewDetails').text('No details available');
                    }

                    // Set status
                    $('#viewStatus').text(product.is_advertised ? 'Advertised' : 'Normal');

                    // Set discount
                    if (product.discount > 0) {
                        $('#viewDiscount').text(product.discount + '% OFF');
                    } else {
                        $('#viewDiscount').text('No Discount').removeClass('bg-warning').addClass('bg-secondary');
                    }

                    $('#viewProductModal').modal('show');
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to load product data',
                    confirmButtonText: 'OK'
                });
            }
        });
    }

    // View Product button click handler
    $('.view-product-btn').click(function(e) {
        e.preventDefault();
        var productId = $(this).data('product-id');
        if (productId) {
            loadProductData(productId);
        }
    });

    // CSRF Setup for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Reset forms when modals are hidden
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form')[0]?.reset();
        $(this).find('.is-invalid').removeClass('is-invalid');
        $(this).find('.invalid-feedback').hide();
    });

    // Add zoom animation class to modals when they are about to show
    $('.modal').on('show.bs.modal', function() {
        $(this).addClass('zoom-modal');
    });

    // Remove zoom animation class from modals when they are hidden
    $('[data-bs-toggle="modal"]').css({
        'transition': 'none',
        'transform': 'none'
    });

    // Initialize Stock Status Chart - Beautiful Multi-line Chart
    const stockCtx = document.getElementById('stockStatusChart');
    if (stockCtx) {
        const stockChart = stockCtx.getContext('2d');

        // Create gradient backgrounds for each line
        const createGradient = (ctx, color1, color2) => {
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, color1);
            gradient.addColorStop(1, color2);
            return gradient;
        };

        // Prepare data for line chart
        const stockRawData = window.chartData.stockChartData || [];
        const labels = stockRawData.map(item => item.date);
        const outOfStockData = stockRawData.map(item => item.out_of_stock);
        const lowStockData = stockRawData.map(item => item.low_stock);
        const mediumStockData = stockRawData.map(item => item.medium_stock);
        const highStockData = stockRawData.map(item => item.high_stock);

        const stockData = {
            labels: labels,
            datasets: [
                {
                    label: 'Out of Stock',
                    data: outOfStockData,
                    borderColor: '#ff4757',
                    backgroundColor: createGradient(stockChart, 'rgba(255, 71, 87, 0.2)', 'rgba(255, 71, 87, 0.05)'),
                    borderWidth: 4,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#ff4757',
                    pointBorderWidth: 3,
                    pointRadius: 8,
                    pointHoverRadius: 12,
                    pointHoverBackgroundColor: '#ff4757',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 4,
                    shadowColor: 'rgba(255, 71, 87, 0.3)',
                    shadowBlur: 10
                },
                {
                    label: 'Low Stock (1-10)',
                    data: lowStockData,
                    borderColor: '#ffa726',
                    backgroundColor: createGradient(stockChart, 'rgba(255, 167, 38, 0.2)', 'rgba(255, 167, 38, 0.05)'),
                    borderWidth: 4,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#ffa726',
                    pointBorderWidth: 3,
                    pointRadius: 8,
                    pointHoverRadius: 12,
                    pointHoverBackgroundColor: '#ffa726',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 4,
                    shadowColor: 'rgba(255, 167, 38, 0.3)',
                    shadowBlur: 10
                },
                {
                    label: 'Medium Stock (11-50)',
                    data: mediumStockData,
                    borderColor: '#42a5f5',
                    backgroundColor: createGradient(stockChart, 'rgba(66, 165, 245, 0.2)', 'rgba(66, 165, 245, 0.05)'),
                    borderWidth: 4,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#42a5f5',
                    pointBorderWidth: 3,
                    pointRadius: 8,
                    pointHoverRadius: 12,
                    pointHoverBackgroundColor: '#42a5f5',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 4,
                    shadowColor: 'rgba(66, 165, 245, 0.3)',
                    shadowBlur: 10
                },
                {
                    label: 'High Stock (51+)',
                    data: highStockData,
                    borderColor: '#66bb6a',
                    backgroundColor: createGradient(stockChart, 'rgba(102, 187, 106, 0.2)', 'rgba(102, 187, 106, 0.05)'),
                    borderWidth: 4,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#66bb6a',
                    pointBorderWidth: 3,
                    pointRadius: 8,
                    pointHoverRadius: 12,
                    pointHoverBackgroundColor: '#66bb6a',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 4,
                    shadowColor: 'rgba(102, 187, 106, 0.3)',
                    shadowBlur: 10
                }
            ]
        };

        const stockConfig = {
            type: 'line',
            data: stockData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 25,
                            font: {
                                size: 13,
                                weight: 'bold',
                                family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                            },
                            color: '#495057'
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(33, 37, 41, 0.95)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255,255,255,0.2)',
                        borderWidth: 2,
                        cornerRadius: 8,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 15,
                        callbacks: {
                            title: function(context) {
                                return '📅 ' + context[0].label;
                            },
                            label: function(context) {
                                const emoji = context.datasetIndex === 0 ? '🚫' :
                                            context.datasetIndex === 1 ? '⚠️' :
                                            context.datasetIndex === 2 ? '📦' : '✅';
                                return emoji + ' ' + context.dataset.label + ': ' + context.parsed.y + ' products';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: '📅 Date',
                            font: {
                                size: 16,
                                weight: 'bold',
                                family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                            },
                            color: '#495057'
                        },
                        grid: {
                            display: true,
                            color: 'rgba(0,0,0,0.05)',
                            borderDash: [5, 5]
                        },
                        ticks: {
                            font: {
                                size: 12,
                                weight: '500'
                            },
                            color: '#6c757d'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: '📊 Number of Products',
                            font: {
                                size: 16,
                                weight: 'bold',
                                family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                            },
                            color: '#495057'
                        },
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.08)',
                            borderDash: [3, 3]
                        },
                        ticks: {
                            font: {
                                size: 12,
                                weight: '500'
                            },
                            color: '#6c757d',
                            callback: function(value) {
                                return value + ' items';
                            }
                        }
                    }
                },
                animation: {
                    duration: 2500,
                    easing: 'easeInOutCubic',
                    delay: function(context) {
                        return context.dataIndex * 200;
                    }
                },
                elements: {
                    point: {
                        hoverRadius: 12,
                        hoverBorderWidth: 4
                    },
                    line: {
                        borderCapStyle: 'round',
                        borderJoinStyle: 'round'
                    }
                },
                layout: {
                    padding: {
                        top: 20,
                        right: 20,
                        bottom: 20,
                        left: 20
                    }
                }
            }
        };

        // Custom plugin for glow effect
        const glowPlugin = {
            id: 'glow',
            beforeDraw: (chart) => {
                const { ctx } = chart;
                ctx.save();

                chart.data.datasets.forEach((dataset, datasetIndex) => {
                    if (dataset.shadowColor && dataset.shadowBlur) {
                        const meta = chart.getDatasetMeta(datasetIndex);
                        meta.dataset.options.shadowColor = dataset.shadowColor;
                        meta.dataset.options.shadowBlur = dataset.shadowBlur;
                    }
                });

                ctx.restore();
            }
        };

        // Register the custom plugin
        if (!Chart.registry.plugins.get('glow')) {
            Chart.register(glowPlugin);
        }

        new Chart(stockChart, stockConfig);
    }

    // Enhanced legend styling with current values
    $('.stock-legend-item').each(function(index) {
        const colors = ['#ff4757', '#ffa726', '#42a5f5', '#66bb6a'];
        const emojis = ['🚫', '⚠️', '📦', '✅'];

        $(this).find('.legend-color').css({
            'width': '16px',
            'height': '16px',
            'border-radius': '50%',
            'display': 'inline-block',
            'margin-right': '8px',
            'border': '3px solid rgba(255,255,255,0.9)',
            'box-shadow': '0 3px 8px rgba(0,0,0,0.15)',
            'background': `linear-gradient(135deg, ${colors[index]}, ${colors[index]}dd)`,
            'position': 'relative'
        });

        // Add emoji to the legend text
        const textElement = $(this).find('small, strong');
        if (textElement.length > 0 && !textElement.text().includes('🚫') && !textElement.text().includes('⚠️')) {
            textElement.first().prepend(emojis[index] + ' ');
        }
    });

    // Add some sparkle animation to the chart container
    $('.chart-container').css({
        'position': 'relative',
        'overflow': 'hidden'
    });

    // Add subtle background pattern
    $('.chart-container').prepend(`
        <div style="
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.03) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.03) 0%, transparent 50%),
                        radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.03) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        "></div>
    `);

    $('#stockStatusChart').css('position', 'relative').css('z-index', '1');

    // Initialize Category Overview Chart - Clean and Attractive Bar Chart
    const categoryCanvas = document.getElementById('categoryChart');
    if (categoryCanvas) {
        // Destroy existing chart if it exists
        if (window.categoryChart instanceof Chart) {
            window.categoryChart.destroy();
        }

        const categoryCtx = categoryCanvas.getContext('2d');

        // Prepare data for bar chart
        const categoryRawData = window.chartData.categoryBreakdown || [];
        const categoryLabels = categoryRawData.map(item => item.name);
        const categoryData = categoryRawData.map(item => item.count);

        // Simple bar chart configuration
        const categoryConfig = {
            type: 'bar',
            data: {
                labels: categoryLabels,
                datasets: [
                    {
                        label: 'Products per Category',
                        data: categoryData,
                        backgroundColor: [
                            'rgba(255, 107, 157, 0.8)',
                            'rgba(78, 205, 196, 0.8)',
                            'rgba(69, 183, 209, 0.8)',
                            'rgba(254, 202, 87, 0.8)',
                            'rgba(108, 92, 231, 0.8)',
                            'rgba(253, 121, 168, 0.8)',
                            'rgba(0, 184, 148, 0.8)',
                            'rgba(253, 203, 110, 0.8)'
                        ],
                        borderColor: [
                            '#FF6B9D',
                            '#4ECDC4',
                            '#45B7D1',
                            '#FECA57',
                            '#6C5CE7',
                            '#FD79A8',
                            '#00B894',
                            '#FDCB6E'
                        ],
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                        hoverBackgroundColor: [
                            'rgba(255, 107, 157, 1)',
                            'rgba(78, 205, 196, 1)',
                            'rgba(69, 183, 209, 1)',
                            'rgba(254, 202, 87, 1)',
                            'rgba(108, 92, 231, 1)',
                            'rgba(253, 121, 168, 1)',
                            'rgba(0, 184, 148, 1)',
                            'rgba(253, 203, 110, 1)'
                        ]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        cornerRadius: 6,
                        callbacks: {
                            title: function(context) {
                                return '📂 ' + context[0].label;
                            },
                            label: function(context) {
                                return 'Products: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Categories'
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Product Count'
                        },
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' items';
                            }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart',
                    delay: function(context) {
                        return context.dataIndex * 100;
                    }
                }
            }
        };

        // Create the chart and store reference
        window.categoryChart = new Chart(categoryCtx, categoryConfig);
    }

    // Add sparkle animation to category chart
    $('#categoryChart').parent('.chart-container').css({
        'position': 'relative',
        'overflow': 'hidden'
    });

    // Add beautiful background pattern for category chart
    $('#categoryChart').parent('.chart-container').prepend(`
        <div style="
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(circle at 25% 25%, rgba(255, 107, 157, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 75% 25%, rgba(78, 205, 196, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 25% 75%, rgba(69, 183, 209, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(254, 202, 87, 0.08) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
            animation: gentlePulse 4s ease-in-out infinite;
        "></div>
    `);

    $('#categoryChart').css('position', 'relative').css('z-index', '1');

    // Add CSS animation for legend items
    if (!$('#legendAnimation').length) {
        $('head').append(`
            <style id="legendAnimation">
                @keyframes slideInLeft {
                    from {
                        opacity: 0;
                        transform: translateX(-20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateX(0);
                    }
                }

        @keyframes gentlePulse {
                    0%, 100% {
                        opacity: 0.6;
                        transform: scale(1);
                    }
                    50% {
                        opacity: 1;
                        transform: scale(1.05);
                    }
                }

                @keyframes riseUp {
                    from {
                        opacity: 0;
                        transform: translateY(50px) scale(0.8);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0) scale(1);
                    }
                }

                @keyframes rotate {
                    from {
                        transform: rotate(0deg);
                    }
                    to {
                        transform: rotate(360deg);
                    }
                }

                @keyframes shimmer {
                    0% {
                        opacity: 0;
                        transform: translateX(-100%);
                    }
                    50% {
                        opacity: 0.6;
                    }
                    100% {
                        opacity: 0;
                        transform: translateX(100%);
                    }
                }

                .legend-item {
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                }

                .legend-item:hover .color-dot {
                    transform: scale(1.2);
                    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                }
            </style>
        `);
    }
});
