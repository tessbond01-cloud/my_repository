(function ($) {
    'use strict';

    const WF_ProductFilters = {
        rangeTimeout: null,
        swatchTimeout: null,
        filterTimeout: null,
        selectedNCSColor: null,
        selectedSegmentPath: null,
        highlightLine: null,

        // Initialize the filtering system
        init: function () {
            this.bindEvents();
            this.initRangeSliders();
            this.initColorPicker();
            this.initAdvancedFiltersToggle();
        },

        // Bind event handlers
        bindEvents: function () {
            // Filter form submission
            $(document).on('submit', '#wf-product-filters', this.handleFormSubmit);
            $(document).on('click', '#wf-clear-filters', this.clearFilters);
            $(document).on('click', '#wf-apply-filters', this.applyFilters);

            // Checkbox changes
            $(document).on('change', '#wf-product-filters input[type="checkbox"]', this.handleFilterChange);

            // Range input changes
            $(document).on('input change', '#wf-product-filters input[type="number"]', this.handleRangeChange);

            // Select changes
            $(document).on('change', '#wf-product-filters select', this.handleSelectChange);

            // Color swatch clicks
            $(document).on('click', '.wf-color-swatch', this.handleColorSwatchClick);

            // Color picker modal
            $(document).on('click', '#wf-color-picker-btn', this.openColorPicker);
            $(document).on('click', '.wf-color-modal-close, #wf-color-cancel', this.closeColorPicker);
            $(document).on('click', '#wf-color-apply', this.applySelectedColor);

            // Advanced filters toggle
            $(document).on('click', '#wf-toggle-advanced-filters-btn', this.toggleAdvancedFilters);

            // Mobile filter toggle
            $(document).on('click', '#wf-mobile-filter-toggle', this.toggleMobileFilters);

            // Color filter buttons
            $(document).on('click', '.wf-color-btn', this.handleColorFilterClick);

            // Sorting buttons
            $(document).on('click', '.wf-sort-btn', this.handleSortClick);

            // Tab switching
            $(document).on('click', '.wf-tab-link', this.handleTabSwitch);
        },

        // Initialize advanced filters toggle functionality
        initAdvancedFiltersToggle: function () {
            const $toggleBtn = $('#wf-toggle-advanced-filters-btn');
            const $filtersArea = $('#wf-advanced-filters-area');

            // Ensure initial hidden state
            $filtersArea.removeClass('open').hide();
            $toggleBtn.removeClass('active');

            console.log('Advanced filters initialized - hidden by default');
        },

        // Toggle advanced filters visibility
        toggleAdvancedFilters: function (e) {
            e.preventDefault();

            const $toggleBtn = $('#wf-toggle-advanced-filters-btn');
            const $filtersArea = $('#wf-advanced-filters-area');

            console.log('Toggle button clicked');

            if ($filtersArea.hasClass('open')) {
                // Hide filters with animation
                console.log('Hiding filters...');
                $filtersArea.removeClass('open').slideUp(300);
                $toggleBtn.removeClass('active');
                $toggleBtn.find('i').removeClass('fa-times').addClass('fa-sliders-h');
                console.log('Filters hidden');
            } else {
                // Show filters with animation
                console.log('Showing filters...');
                $filtersArea.addClass('open').slideDown(300);
                $toggleBtn.addClass('active');
                $toggleBtn.find('i').removeClass('fa-sliders-h').addClass('fa-times');
                console.log('Filters shown');
            }
        },

        // Handle range input changes
        handleRangeChange: function () {
            clearTimeout(WF_ProductFilters.rangeTimeout);
            WF_ProductFilters.rangeTimeout = setTimeout(function () {
                WF_ProductFilters.applyFilters();
            }, 500);
        },

        // Handle select changes
        handleSelectChange: function () {
            WF_ProductFilters.applyFilters();
        },

        // Initialize range sliders
        initRangeSliders: function () {
            this.initDualRangeSlider('chromaticness');
            this.initDualRangeSlider('blackness');
        },

        // Initialize dual-control range slider
        initDualRangeSlider: function (type) {
            const minSlider = $(`#${type}-range-min`);
            const maxSlider = $(`#${type}-range-max`);
            const minInput = $(`#${type}-min`);
            const maxInput = $(`#${type}-max`);
            const progress = $(`#${type}-progress`);

            if (!minSlider.length || !maxSlider.length || !minInput.length || !maxInput.length) {
                return; // Elements don't exist
            }

            // Update progress bar
            const updateProgress = () => {
                const min = parseInt(minSlider.val());
                const max = parseInt(maxSlider.val());

                progress.css({
                    'left': min + '%',
                    'width': (max - min) + '%'
                });
            };

            // Min slider changes
            minSlider.on('input', function () {
                const value = parseInt($(this).val());
                const maxValue = parseInt(maxSlider.val());

                if (value > maxValue) {
                    $(this).val(maxValue);
                }

                minInput.val($(this).val());
                updateProgress();
            });

            // Max slider changes
            maxSlider.on('input', function () {
                const value = parseInt($(this).val());
                const minValue = parseInt(minSlider.val());

                if (value < minValue) {
                    $(this).val(minValue);
                }

                maxInput.val($(this).val());
                updateProgress();
            });

            // Min input changes
            minInput.on('input change', function () {
                let value = parseInt($(this).val());

                if (isNaN(value)) value = 0;
                if (value < 0) value = 0;
                if (value > 100) value = 100;

                const maxValue = parseInt(maxInput.val());
                if (value > maxValue) {
                    value = maxValue;
                }

                $(this).val(value);
                minSlider.val(value);
                updateProgress();
            });

            // Max input changes
            maxInput.on('input change', function () {
                let value = parseInt($(this).val());

                if (isNaN(value)) value = 100;
                if (value < 0) value = 0;
                if (value > 100) value = 100;

                const minValue = parseInt(minInput.val());
                if (value < minValue) {
                    value = minValue;
                }

                $(this).val(value);
                maxSlider.val(value);
                updateProgress();
            });

            // Initial update
            updateProgress();

            // Trigger filter update with debounce
            minSlider.add(maxSlider).add(minInput).add(maxInput).on('input change', function () {
                clearTimeout(WF_ProductFilters.rangeTimeout);
                WF_ProductFilters.rangeTimeout = setTimeout(function () {
                    WF_ProductFilters.applyFilters();
                }, 500);
            });
        },

        // Initialize color picker
        initColorPicker: function () {
            const container = document.getElementById('wf-ncs-color-circle');
            if (!container) return;

            this.createSVGColorWheel(container);
        },

        // Create SVG color wheel with complete NCS specification
        createSVGColorWheel: function (container) {
            const size = 400; // Increased size for better visibility
            const centerX = size / 2;
            const centerY = size / 2;
            const radius = 150; // Increased radius

            // Clear container
            container.innerHTML = '';

            // Create SVG element
            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('width', '100%');
            svg.setAttribute('height', '100%');
            svg.setAttribute('viewBox', `0 0 ${size} ${size}`);
            svg.style.cursor = 'pointer';

            // Create highlight line (initially hidden)
            this.highlightLine = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            this.highlightLine.setAttribute('x1', centerX);
            this.highlightLine.setAttribute('y1', centerY);
            this.highlightLine.setAttribute('x2', centerX);
            this.highlightLine.setAttribute('y2', centerY - radius);
            this.highlightLine.setAttribute('stroke', '#fff');
            this.highlightLine.setAttribute('stroke-width', '3');
            this.highlightLine.setAttribute('stroke-linecap', 'round');
            this.highlightLine.style.display = 'none';
            svg.appendChild(this.highlightLine);

            // Create 40 segments
            const colors = ['Y', 'R', 'B', 'G'];

            for (let i = 0; i < 40; i++) {
                const angle = (i * 9) - 90; // Start from top
                const startAngle = angle;
                const endAngle = angle + 9;
                const midAngle = (startAngle + endAngle) / 2;

                // Determine segment label
                const quadrant = Math.floor(i / 10);
                const position = (i % 10 + 1) * 10;
                const fromColor = colors[quadrant];
                const toColor = colors[(quadrant + 1) % 4];
                const segmentLabel = `${fromColor}${position}${toColor}`;

                // Improved HSL color interpolation
                const color = this.interpolateHSLColor(fromColor, toColor, position / 100);

                // Create path
                const startAngleRad = (startAngle * Math.PI) / 180;
                const endAngleRad = (endAngle * Math.PI) / 180;

                const x1 = centerX + radius * Math.cos(startAngleRad);
                const y1 = centerY + radius * Math.sin(startAngleRad);
                const x2 = centerX + radius * Math.cos(endAngleRad);
                const y2 = centerY + radius * Math.sin(endAngleRad);

                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                const pathData = `M ${centerX} ${centerY} L ${x1} ${y1} A ${radius} ${radius} 0 0 1 ${x2} ${y2} Z`;

                path.setAttribute('d', pathData);
                path.setAttribute('fill', color);
                path.setAttribute('stroke', '#333');
                path.setAttribute('stroke-width', '1');
                path.setAttribute('data-segment', segmentLabel);
                path.setAttribute('data-mid-angle', midAngle);
                path.style.cursor = 'pointer';

                // Add hover effect
                path.addEventListener('mouseenter', function () {
                    if (this !== WF_ProductFilters.selectedSegmentPath) {
                        this.setAttribute('stroke-width', '2');
                        this.setAttribute('stroke', '#000');
                    }
                });

                path.addEventListener('mouseleave', function () {
                    if (this !== WF_ProductFilters.selectedSegmentPath) {
                        this.setAttribute('stroke-width', '1');
                        this.setAttribute('stroke', '#333');
                    }
                });

                // Add click handler
                path.addEventListener('click', function () {
                    WF_ProductFilters.handleSegmentClick(segmentLabel, this);
                });

                svg.appendChild(path);

                // Add segment label
                const midAngleRad = (midAngle * Math.PI) / 180;
                const labelX = centerX + (radius + 20) * Math.cos(midAngleRad);
                const labelY = centerY + (radius + 20) * Math.sin(midAngleRad);

                const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                text.setAttribute('x', labelX);
                text.setAttribute('y', labelY);
                text.setAttribute('text-anchor', 'middle');
                text.setAttribute('dominant-baseline', 'middle');
                text.setAttribute('font-size', '10');
                text.setAttribute('fill', '#666');
                text.setAttribute('font-family', 'Arial, sans-serif');
                text.textContent = segmentLabel;
                svg.appendChild(text);
            }

            // Add center circle with black fill
            const centerCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            centerCircle.setAttribute('cx', centerX);
            centerCircle.setAttribute('cy', centerY);
            centerCircle.setAttribute('r', '25');
            centerCircle.setAttribute('fill', '#000'); // Changed to black
            centerCircle.setAttribute('stroke', '#333');
            centerCircle.setAttribute('stroke-width', '2');
            svg.appendChild(centerCircle);

            // Add perfectly aligned axis labels
            const axisLabels = [
                { text: 'Y', x: centerX, y: centerY - radius - 35 }, // North
                { text: 'R', x: centerX + radius + 35, y: centerY + 5 }, // East
                { text: 'B', x: centerX, y: centerY + radius + 40 }, // South
                { text: 'G', x: centerX - radius - 35, y: centerY + 5 } // West
            ];

            axisLabels.forEach(label => {
                const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                text.setAttribute('x', label.x);
                text.setAttribute('y', label.y);
                text.setAttribute('text-anchor', 'middle');
                text.setAttribute('font-family', 'Arial, sans-serif');
                text.setAttribute('font-size', '20');
                text.setAttribute('font-weight', 'bold');
                text.setAttribute('fill', '#d0d0d0ff');
                text.textContent = label.text;
                svg.appendChild(text);
            });

            container.appendChild(svg);
        },

        // Improved HSL color interpolation
        interpolateHSLColor: function (fromColor, toColor, t) {
            const colorHSL = {
                'Y': [60, 100, 50],   // Yellow
                'R': [0, 100, 50],    // Red
                'B': [240, 100, 50],  // Blue
                'G': [120, 100, 50]   // Green
            };

            const fromHSL = colorHSL[fromColor];
            const toHSL = colorHSL[toColor];

            // Handle hue wraparound
            let fromH = fromHSL[0];
            let toH = toHSL[0];

            if (Math.abs(toH - fromH) > 180) {
                if (toH > fromH) {
                    fromH += 360;
                } else {
                    toH += 360;
                }
            }

            const h = (fromH + (toH - fromH) * t) % 360;
            const s = fromHSL[1] + (toHSL[1] - fromHSL[1]) * t;
            const l = fromHSL[2] + (toHSL[2] - fromHSL[2]) * t;

            return `hsl(${h}, ${s}%, ${l}%)`;
        },

        // Handle segment click (Issue 4 fix)
        handleSegmentClick: function (segmentLabel, pathElement) {
            // Store selected color
            this.selectedNCSColor = segmentLabel;

            // Reset previous selection
            if (this.selectedSegmentPath) {
                this.selectedSegmentPath.setAttribute('stroke', '#333');
                this.selectedSegmentPath.setAttribute('stroke-width', '1');
            }

            // Highlight current selection
            this.selectedSegmentPath = pathElement;
            pathElement.setAttribute('stroke', '#fff');
            pathElement.setAttribute('stroke-width', '3');

            // Update highlight line
            const midAngle = parseFloat(pathElement.getAttribute('data-mid-angle'));
            const midAngleRad = (midAngle * Math.PI) / 180;
            const size = 400;
            const centerX = size / 2;
            const centerY = size / 2;
            const radius = 150;

            const lineEndX = centerX + radius * Math.cos(midAngleRad);
            const lineEndY = centerY + radius * Math.sin(midAngleRad);

            this.highlightLine.setAttribute('x2', lineEndX);
            this.highlightLine.setAttribute('y2', lineEndY);
            this.highlightLine.style.display = 'block';

            console.log('Selected NCS color:', segmentLabel);
        },

        // Handle color swatch clicks
        handleColorSwatchClick: function (e) {
            e.preventDefault();

            const $swatch = $(this);
            const colorValue = $swatch.data('color');

            // Toggle active state
            if ($swatch.hasClass('active')) {
                $swatch.removeClass('active');
                $('#wf-ncs-color-taka-value').val('');
            } else {
                $('.wf-color-swatch').removeClass('active');
                $swatch.addClass('active');
                $('#wf-ncs-color-taka-value').val(colorValue);
            }

            // Apply filters
            WF_ProductFilters.applyFilters();
        },

        // Handle filter change
        handleFilterChange: function () {
            // Debounce filter application
            clearTimeout(WF_ProductFilters.swatchTimeout);
            WF_ProductFilters.swatchTimeout = setTimeout(function () {
                WF_ProductFilters.applyFilters();
            }, 300);
        },

        // Handle form submit
        handleFormSubmit: function (e) {
            e.preventDefault();
            WF_ProductFilters.applyFilters();
        },

        // Clear all filters
        clearFilters: function (e) {
            e.preventDefault();

            // Clear all form inputs
            $('#wf-product-filters')[0].reset();

            // Clear hidden inputs
            $('#wf-ncs-ending-value').val('');

            // Clear color swatch selections
            $('.wf-color-swatch').removeClass('active');

            // Reset range sliders
            $('#chromaticness-min, #blackness-min').val(0);
            $('#chromaticness-max, #blackness-max').val(100);
            $('#chromaticness-range-min, #blackness-range-min').val(0);
            $('#chromaticness-range-max, #blackness-range-max').val(100);

            // Update progress bars
            $('#chromaticness-progress, #blackness-progress').css({
                'left': '0%',
                'width': '100%'
            });

            // Clear color picker selection
            if (WF_ProductFilters.selectedSegmentPath) {
                WF_ProductFilters.selectedSegmentPath.setAttribute('stroke', '#333');
                WF_ProductFilters.selectedSegmentPath.setAttribute('stroke-width', '1');
                WF_ProductFilters.selectedSegmentPath = null;
            }

            if (WF_ProductFilters.highlightLine) {
                WF_ProductFilters.highlightLine.style.display = 'none';
            }

            WF_ProductFilters.selectedNCSColor = null;

            // Clear color category filter buttons
            $('.wf-color-btn').removeClass('active');
            $('#wf-color-category-input').remove();

            // Apply filters (which will show all products)
            WF_ProductFilters.applyFilters();
        },

        // Apply filters via AJAX
        applyFilters: function () {
            const $form = $('#wf-product-filters');
            const $container = $('#wf-products-container');

            if (!$form.length || !$container.length) {
                return;
            }

            // Add loading state
            $container.addClass('wf-loading');

            // Get form data
            const formData = $form.serialize();

            // Get current page
            const urlParams = new URLSearchParams(window.location.search);
            const page = urlParams.get('paged') || 1;

            // Update URL without page reload
            const newUrl = window.location.pathname + '?' + formData;
            history.pushState(null, '', newUrl);

            // Add page parameter and action
            const data = formData + '&page=' + page + '&action=wf_filter_products';

            // Make AJAX request
            $.ajax({
                url: wf_ajax.ajax_url,
                type: 'POST',
                data: data,
                success: function (response) {
                    if (response.success) {
                        $container.html(response.data.products);

                        // Update pagination if exists
                        if (response.data.pagination) {
                            $('.wf-pagination').html(response.data.pagination);
                        }

                        // Update results count if exists
                        if (response.data.results_count) {
                            $('.wf-results-count').html(response.data.results_count);
                        }

                        // Update visible product count
                        if (response.data.visible_count) {
                            $('#wf-visible-product-count').text(response.data.visible_count);
                        }
                    }
                },
                error: function () {
                    console.log('Filter request failed');
                },
                complete: function () {
                    $container.removeClass('wf-loading');
                }
            });
        },

        // Open color picker modal
        openColorPicker: function (e) {
            e.preventDefault();
            $('#wf-color-modal').fadeIn(300);
        },

        // Close color picker modal
        closeColorPicker: function (e) {
            e.preventDefault();
            $('#wf-color-modal').fadeOut(300);
        },

        // Apply selected color (Updated for _ncs_ending meta field)
        applySelectedColor: function (e) {
            e.preventDefault();

            if (WF_ProductFilters.selectedNCSColor) {
                // Set the hidden input field value with the selected NCS color
                $('#wf-ncs-ending-value').val(WF_ProductFilters.selectedNCSColor);
            }

            // Close modal
            WF_ProductFilters.closeColorPicker(e);
            // Apply filters to trigger AJAX update
            WF_ProductFilters.applyFilters();
        },

        // Toggle mobile filters visibility
        toggleMobileFilters: function (e) {
            e.preventDefault();

            const $container = $('.wf-filter-container');
            const $button = $('#wf-mobile-filter-toggle');
            const $icon = $button.find('i');

            // Toggle the filters container
            $container.toggleClass('wf-filters-open');

            // Update button text and icon based on state
            if ($container.hasClass('wf-filters-open')) {
                // Filters are now open - show close state
                $button.html('<i class="fas fa-times"></i> Sulje suodattimet');
            } else {
                // Filters are now closed - show open state
                $button.html('<i class="fas fa-filter"></i> Suodattimet');
            }
        },

        // Handle color filter button clicks - REFACTORED IMPLEMENTATION
        handleColorFilterClick: function (e) {
            e.preventDefault();

            const $button = $(this);
            const color = $button.data('color');
            const isNeutral = ['valkoinen', 'vaaleanharmaa', 'tummanharmaa', 'musta'].includes(color);

            // Toggle active class for the clicked button
            $button.toggleClass('active');

            // Remove existing hidden input for this color to prevent duplicates
            $(`#wf-product-filters input[name="${isNeutral ? 'blackness_preset[]' : '_color_category[]'}"][value="${color}"]`).remove();

            if ($button.hasClass('active')) {
                // Add a new hidden input
                const inputName = isNeutral ? 'blackness_preset[]' : '_color_category[]';
                const newInput = `<input type="hidden" name="${inputName}" value="${color}">`;
                $('#wf-product-filters').append(newInput);
            }

            // Apply filters to trigger AJAX request
            WF_ProductFilters.applyFilters();
        },

        // Handle sorting button clicks
        handleSortClick: function (e) {
            e.preventDefault();

            const $button = $(this);
            const sortType = $button.data('sort');

            // Toggle active state
            $('.wf-sort-btn').removeClass('active');
            $button.addClass('active');

            // Set hidden sort input (add to form if doesn't exist)
            let $sortInput = $('#wf-sort-input');
            if (!$sortInput.length) {
                $sortInput = $('<input type="hidden" name="sort_by" id="wf-sort-input">');
                $('#wf-product-filters').append($sortInput);
            }
            $sortInput.val(sortType);

            // Apply filters with sorting
            WF_ProductFilters.applyFilters();
        },

        // Handle tab switching
        handleTabSwitch: function (e) {
            e.preventDefault();

            const $tab = $(this);
            const tabName = $tab.data('tab');

            // Update tab states
            $('.wf-tab-link').removeClass('active');
            $tab.addClass('active');

            // Show/hide content panels
            $('.wf-tab-panel').hide();
            $(`#wf-tab-content-${tabName}`).show();
        },

        // Handle filter changes with debouncing
        handleFilterChange: function () {
            clearTimeout(WF_ProductFilters.filterTimeout);
            WF_ProductFilters.filterTimeout = setTimeout(function () {
                WF_ProductFilters.applyFilters();
            }, 300);
        }
    };

    // Initialize when document is ready
    $(document).ready(function () {
        WF_ProductFilters.init();
    });

})(jQuery);
