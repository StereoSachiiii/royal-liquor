<?php
/**
 * Admin Dashboard - HTML Template Registry
 * Centralized store for reusable UI components using native <template> tags.
 */
?>

<!-- ==========================================================================
     BASE COMPONENT TEMPLATES
     ========================================================================== -->

<!-- Entity List Container (Header, Search, Table) -->
<template id="tpl-admin-entity">
    <div class="admin-entity animate-fade p-6 lg:p-12 mb-20">
        <!-- Page Header -->
        <div class="flex flex-col lg:flex-row lg:items-end justify-between mb-12 gap-6">
            <div class="space-y-2">
                <h2 class="text-4xl font-heading font-black tracking-tighter text-black m-0 italic">{{entity-title}}</h2>
                <div class="h-1 w-12 bg-black"></div>
                <p class="text-gray-500 text-xs uppercase tracking-[0.2em] font-black">{{entity-subtitle}}</p>
            </div>
        </div>
 
        <!-- Main Card Container -->
        <div class="bg-white border border-gray-100 shadow-sm rounded-none overflow-hidden">
            <!-- Table Header: Search & Controls -->
            <div class="px-8 py-8 border-b border-gray-50 bg-white flex flex-col lg:flex-row justify-between items-center gap-8">
                <div class="flex items-center gap-8 w-full mr-auto">
                    <!-- Modern Underline Search -->
                    <div class="relative w-full max-w-md group">
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 text-black text-lg transition-transform group-focus-within:scale-110">🔍</span>
                        <input type="search" placeholder="SEARCH {{entity-title}}..." 
                               class="w-full bg-transparent border-t-0 border-x-0 border-b-2 border-gray-100 py-3 pl-10 pr-4 text-xs font-black tracking-widest focus:ring-0 focus:border-black transition-all placeholder:text-gray-300" 
                               id="entity-search">
                    </div>
                    
                    <!-- Modern Select Wrapper -->
                    <div class="relative hidden sm:block">
                        <select class="appearance-none bg-transparent border-none pr-8 py-2 text-[10px] font-black uppercase tracking-widest text-gray-500 focus:ring-0 focus:text-black cursor-pointer transition-colors" id="entity-sort">
                            <option value="newest">Latest Entry</option>
                            <option value="oldest">Historical</option>
                        </select>
                        <span class="absolute right-0 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">▼</span>
                    </div>
                </div>
 
                <div class="flex items-center gap-4 shrink-0">
                    <button class="w-12 h-12 flex items-center justify-center border border-gray-100 text-gray-400 hover:border-black hover:text-black transition-all group" id="entity-refresh-btn" title="Refresh Sync">
                        <span class="text-xl group-active:rotate-90 transition-transform">🔄</span>
                    </button>
                    <button class="btn-premium px-8 py-4" id="entity-create-btn">
                        <span>ADD NEW</span>
                    </button>
                </div>
            </div>
 
            <!-- Table Body -->
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead id="entity-thead" class="bg-gray-50 border-b border-gray-100">
                        <!-- Column headers injected here -->
                    </thead>
                    <tbody id="entity-tbody" class="divide-y divide-gray-50">
                        <!-- Rows injected here -->
                    </tbody>
                </table>
            </div>
 
            <!-- Footer Actions -->
            <div class="flex justify-center p-12 border-t border-gray-50 bg-white hidden" id="entity-load-more-container">
                 <button id="entity-load-more-btn" class="btn-premium-outline px-16">LOAD MORE RESULTS</button>
            </div>
        </div>
    </div>
</template>

<!-- Standard Modal Structure -->
<template id="tpl-admin-modal">
    <div class="modal-overlay animate-fade">
        <div class="modal-card animate-slide" id="modal-container">
            <div class="flex justify-between items-center p-6 border-b bg-slate-50">
                <h3 class="text-xl font-bold text-black" id="modal-title">{{title}}</h3>
                <button class="btn btn-outline p-2 rounded-full border-none hover:bg-slate-200" id="modal-close">&times;</button>
            </div>
            <div class="overflow-y-auto p-6" id="modal-body">
                <!-- Content injected here -->
            </div>
            <div class="p-6 border-t bg-slate-50 flex justify-end gap-3" id="modal-footer">
                <!-- Buttons injected here -->
            </div>
        </div>
    </div>
</template>

<!-- ==========================================================================
     PRODUCT DOMAIN TEMPLATES
     ========================================================================== -->

<!-- Product Table Row -->
<template id="tpl-product-row">
    <tr class="tr group">
        <td class="td font-medium text-slate-500 text-xs">#{{id}}</td>
        <td class="td">
            <div class="flex items-center gap-3">
                <img src="{{image_url}}" class="thumb-md shadow-sm border" alt="{{name}}">
                <div class="flex flex-col">
                    <span class="font-semibold text-black leading-tight">{{name}}</span>
                    <span class="text-xs text-slate-500">{{slug}}</span>
                </div>
            </div>
        </td>
        <td class="td">
            <span class="badge badge-info">{{category_name}}</span>
        </td>
        <td class="td font-bold text-black">${{price}}</td>
        <td class="td">
            <span class="text-xs text-slate-500 block truncate max-w-xs">{{description}}</span>
        </td>
        <td class="td">
            <div class="flex gap-2">
                <button class="btn btn-outline btn-sm js-view" data-id="{{id}}">👁️ View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="{{id}}">✏️ Edit</button>
            </div>
        </td>
    </tr>
</template>

<!-- Product Form (Create/Edit) -->
<template id="tpl-product-form">
    <form class="flex flex-col gap-6" id="product-form">
        <div class="grid grid-cols-2 gap-6">
            <!-- Left Side -->
            <div class="flex flex-col gap-4">
                <div class="form-group">
                    <label class="form-label field-required">Product Name</label>
                    <input type="text" name="name" class="input" placeholder="e.g. Premium Vodka" required value="{{name}}">
                </div>
                <div class="form-group">
                    <label class="form-label field-required">Slug</label>
                    <input type="text" name="slug" class="input" placeholder="premium-vodka" required value="{{slug}}">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label field-required">Price (Cents)</label>
                        <input type="number" name="price_cents" class="input" placeholder="1000" required value="{{price_cents}}">
                    </div>
                    <div class="form-group">
                        <label class="form-label field-required">Category</label>
                        <select name="category_id" class="select" required id="form-category-select">
                            <!-- Options injected -->
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Supplier (Optional)</label>
                    <select name="supplier_id" class="select" id="form-supplier-select">
                        <option value="">No Supplier</option>
                    </select>
                </div>
                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg">
                    <input type="checkbox" name="is_active" class="w-4 h-4" id="is-active-check" {{is_active_checked}}>
                    <label for="is-active-check" class="text-sm font-semibold cursor-pointer">Product is Active</label>
                </div>
            </div>

            <!-- Right Side -->
            <div class="flex flex-col gap-4">
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="textarea text-sm" rows="5" placeholder="Describe the product...">{{description}}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Product Image</label>
                    <div style="border:1px solid #e2e8f0;border-radius:12px;padding:16px;background:#f8fafc;display:flex;align-items:center;gap:12px;">
                        <img id="image-preview" src="{{image_url}}" style="width:64px;height:64px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0;display:{{image_display}};" alt="Preview">
                        <div style="flex:1;">
                            <input type="file" id="prod-file-input" style="display:none;" accept="image/*">
                            <label for="prod-file-input" class="btn btn-outline" style="width:100%;cursor:pointer;display:block;text-align:center;">
                                📷 Upload New Image
                            </label>
                            <input type="hidden" name="image_url" id="image-url-hidden" value="{{image_url}}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t mt-4">
            <button type="button" class="btn btn-outline" id="form-cancel">Cancel</button>
            <button type="submit" class="btn btn-primary px-8" id="form-submit">{{submit_text}}</button>
        </div>
    </form>
</template>


<!-- ==========================================================================
     CATEGORY DOMAIN TEMPLATES
     ========================================================================== -->

<!-- Category Table Row -->
<template id="tpl-category-row">
    <tr class="tr group">
        <td class="td font-medium text-slate-500 text-xs">#{{id}}</td>
        <td class="td">
            <span class="font-semibold text-black">{{name}}</span>
        </td>
        <td class="td text-slate-500 text-sm">{{slug}}</td>
        <td class="td">
            <img src="{{image_url}}" class="thumb-md border shadow-sm" style="display:{{has_image}}" alt="{{name}}">
            <span class="text-slate-400 text-xs" style="display:{{no_image}}">No image</span>
        </td>
        <td class="td">
            <span class="badge badge-info">{{product_count}}</span>
        </td>
        <td class="td">
            <span class="badge {{status_class}}">{{status_label}}</span>
        </td>
        <td class="td text-slate-500 text-xs">{{created_at}}</td>
        <td class="td">
            <div class="flex gap-2">
                <button class="btn btn-outline btn-sm js-view" data-id="{{id}}">👁️ View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="{{id}}">✏️ Edit</button>
            </div>
        </td>
    </tr>
</template>

<!-- Category Form (Create & Edit — JS populates values) -->
<template id="tpl-category-form">
    <form class="flex flex-col gap-6" id="cat-form">
        <div class="grid grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="flex flex-col gap-4">
                <div class="form-group">
                    <label class="form-label field-required">Category Name</label>
                    <input type="text" name="name" class="input" placeholder="e.g. Whiskey" required value="{{name}}">
                </div>

                <div class="form-group">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="input" placeholder="auto-generated if blank" value="{{slug}}">
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="textarea text-sm" rows="5" placeholder="Category description...">{{description}}</textarea>
                </div>
            </div>

            <!-- Right Column -->
            <div class="flex flex-col gap-4">
                <div class="form-group">
                    <label class="form-label">Category Image</label>
                    <div class="flex items-center gap-4 p-4 border rounded-xl bg-slate-50">
                        <img id="cat-image-preview" src="{{image_url}}" class="thumb-lg border shadow-sm" style="display:{{image_display}}" alt="Preview">
                        <div class="flex-1">
                            <input type="file" id="cat-image-file" class="hidden" accept="image/*">
                            <label for="cat-image-file" class="btn btn-outline w-full cursor-pointer">📷 Upload Image</label>
                            <input type="hidden" name="image_url" id="cat-image-hidden" value="{{image_url}}">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg">
                    <input type="checkbox" name="is_active" class="w-4 h-4" id="cat-is-active" {{is_active_checked}}>
                    <label for="cat-is-active" class="text-sm font-semibold cursor-pointer">Category is Active</label>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t mt-2 cat-form-footer">
            <button type="button" class="btn btn-outline" id="cat-cancel">Cancel</button>
            <button type="submit" class="btn btn-primary px-8">{{submit_text}}</button>
        </div>
    </form>
</template>

<!-- ==========================================================================
     SUPPLIER DOMAIN TEMPLATES
     ========================================================================== -->

<!-- Supplier Table Row -->
<template id="tpl-supplier-row">
    <tr class="tr group">
        <td class="td font-medium text-slate-500 text-xs">#{{id}}</td>
        <td class="td">
            <span class="font-semibold text-black">{{name}}</span>
        </td>
        <td class="td text-slate-500 text-sm">{{email}}</td>
        <td class="td text-slate-500 text-sm">{{phone}}</td>
        <td class="td text-slate-500 text-xs truncate max-w-xs">{{address}}</td>
        <td class="td">
            <span class="badge {{status_class}}">{{status_label}}</span>
        </td>
        <td class="td text-slate-500 text-xs">{{created_at}}</td>
        <td class="td">
            <div class="flex gap-2">
                <button class="btn btn-outline btn-sm js-view" data-id="{{id}}">👁️ View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="{{id}}">✏️ Edit</button>
            </div>
        </td>
    </tr>
</template>

<!-- Supplier Form (Create & Edit) -->
<template id="tpl-supplier-form">
    <form class="flex flex-col gap-6" id="sup-form">
        <div class="grid grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="flex flex-col gap-4">
                <div class="form-group">
                    <label class="form-label field-required">Supplier Name</label>
                    <input type="text" name="name" class="input" placeholder="e.g. Premium Spirits Inc." required value="{{name}}">
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="input" placeholder="contact@supplier.com" value="{{email}}">
                </div>

                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="input" placeholder="+1 (555) 123-4567" value="{{phone}}">
                </div>
            </div>

            <!-- Right Column -->
            <div class="flex flex-col gap-4">
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="textarea text-sm" rows="3" placeholder="123 Supply Chain Rd...">{{address}}</textarea>
                </div>

                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg">
                    <input type="checkbox" name="is_active" class="w-4 h-4" id="sup-is-active" {{is_active_checked}}>
                    <label for="sup-is-active" class="text-sm font-semibold cursor-pointer">Supplier is Active</label>
                </div>

                <div id="sup-stats-info" class="bg-white p-4 rounded-lg border {{stats_display}}">
                    <h4 class="text-xs font-bold uppercase text-slate-400 mb-2">Statistics</h4>
                    <div class="text-xs text-slate-500 flex flex-col gap-1">
                        <div class="flex justify-between"><span>Products:</span><span class="font-medium text-black">{{product_count}}</span></div>
                        <div class="flex justify-between"><span>Created:</span><span>{{created_at}}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t mt-2 sup-form-footer">
            <button type="button" class="btn btn-outline" id="sup-cancel">Cancel</button>
            <button type="submit" class="btn btn-primary px-8">{{submit_text}}</button>
        </div>
    </form>
</template>

<!-- ==========================================================================
     WAREHOUSE DOMAIN TEMPLATES
     ========================================================================== -->

<!-- Warehouse Table Row -->
<template id="tpl-warehouse-row">
    <tr class="tr group">
        <td class="td font-medium text-slate-500 text-xs">#{{id}}</td>
        <td class="td">
            <span class="font-semibold text-black">{{name}}</span>
        </td>
        <td class="td text-slate-500 text-sm italic">{{address}}</td>
        <td class="td text-slate-500 text-sm">{{phone}}</td>
        <td class="td">
            <img src="{{image_url}}" class="thumb-md border shadow-sm" style="display:{{has_image}}" alt="{{name}}">
            <span class="text-slate-400 text-xs" style="display:{{no_image}}">-</span>
        </td>
        <td class="td">
            <span class="badge {{status_class}}">{{status_label}}</span>
        </td>
        <td class="td">
            <div class="flex gap-2">
                <button class="btn btn-outline btn-sm js-view" data-id="{{id}}">👁️ View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="{{id}}">✏️ Edit</button>
            </div>
        </td>
    </tr>
</template>

<!-- Warehouse Form (Create & Edit) -->
<template id="tpl-warehouse-form">
    <form class="flex flex-col gap-6" id="war-form">
        <div class="grid grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="flex flex-col gap-4">
                <div class="form-group">
                    <label class="form-label field-required">Warehouse Name</label>
                    <input type="text" name="name" class="input" placeholder="e.g. Main Distribution Center" required value="{{name}}">
                </div>

                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="input" placeholder="123 Warehouse Dr..." value="{{address}}">
                </div>

                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="input" placeholder="+1 (555) 123-4567" value="{{phone}}">
                </div>
            </div>

            <!-- Right Column -->
            <div class="flex flex-col gap-4">
                <div class="form-group">
                    <label class="form-label">Warehouse Image</label>
                    <div class="flex items-center gap-4 p-4 border rounded-xl bg-slate-50">
                        <img id="war-image-preview" src="{{image_url}}" class="thumb-lg border shadow-sm" style="display:{{image_display}}" alt="Preview">
                        <div class="flex-1">
                            <input type="file" id="war-image-file" class="hidden" accept="image/*">
                            <label for="war-image-file" class="btn btn-outline w-full cursor-pointer">📷 Upload Image</label>
                            <input type="hidden" name="image_url" id="war-image-hidden" value="{{image_url}}">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg">
                    <input type="checkbox" name="is_active" class="w-4 h-4" id="war-is-active" {{is_active_checked}}>
                    <label for="war-is-active" class="text-sm font-semibold cursor-pointer">Warehouse is Active</label>
                </div>

                <div id="war-stats-info" class="bg-white p-4 rounded-lg border {{stats_display}}">
                    <h4 class="text-xs font-bold uppercase text-slate-400 mb-2">Inventory Summary</h4>
                    <div class="text-xs text-slate-500 flex flex-col gap-1">
                        <div class="flex justify-between"><span>Stock Entries:</span><span class="font-medium text-black">{{stock_entries}}</span></div>
                        <div class="flex justify-between"><span>Unique Products:</span><span class="font-medium text-black">{{product_count}}</span></div>
                        <div class="flex justify-between"><span>Created:</span><span>{{created_at}}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t mt-2 war-form-footer">
            <button type="button" class="btn btn-outline" id="war-cancel">Cancel</button>
            <button type="submit" class="btn btn-primary px-8">{{submit_text}}</button>
        </div>
    </form>
</template>

<!-- ==========================================================================
     FLAVOR PROFILE DOMAIN TEMPLATES
     ========================================================================== -->

<!-- Flavor Profile Table Row -->
<template id="tpl-flavor-profile-row">
    <tr class="tr group">
        <td class="td font-medium text-slate-500 text-xs">#{{id}}</td>
        <td class="td">
            <div class="flex items-center gap-3">
                <img src="{{image_url}}" class="thumb-md border shadow-sm" style="display:{{has_image}}" alt="{{name}}">
                <div class="flex flex-col">
                    <span class="font-semibold text-black leading-tight">{{name}}</span>
                    <span class="text-xs text-slate-500 italic">{{slug}}</span>
                </div>
            </div>
        </td>
        <td class="td text-center"><span class="badge badge-info">{{sweetness}}</span></td>
        <td class="td text-center"><span class="badge badge-warning">{{bitterness}}</span></td>
        <td class="td text-center"><span class="badge badge-danger">{{strength}}</span></td>
        <td class="td text-center"><span class="badge badge-success">{{fruitiness}}</span></td>
        <td class="td text-center"><span class="badge badge-secondary">{{spiciness}}</span></td>
        <td class="td">
            <div class="flex gap-2">
                <button class="btn btn-outline btn-sm js-view" data-id="{{id}}">👁️ View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="{{id}}">✏️ Edit</button>
            </div>
        </td>
    </tr>
</template>

<!-- Flavor Profile Form (Create & Edit) -->
<template id="tpl-flavor-profile-form">
    <form class="flex flex-col gap-6" id="flap-form">
        <!-- Section: Product (Only visible or enabled in Create mode) -->
        <div id="flap-product-section" class="bg-slate-50 p-4 rounded-xl border {{product_section_display}}">
            <h4 class="text-xs font-bold uppercase text-slate-400 mb-3">1. Select Product</h4>
            <div class="form-group">
                <label class="form-label field-required">Target Product</label>
                <select name="product_id" class="select" id="flap-product-select" {{product_disabled}}>
                    <!-- Product options -->
                </select>
            </div>
        </div>

        <!-- Section: Visual Info (Only visible in Edit mode) -->
        <div id="flap-edit-header" class="flex items-center gap-4 pb-4 border-b {{edit_header_display}}">
            <img src="{{image_url}}" class="thumb-lg border shadow-sm" style="display:{{image_display}}" alt="Product">
            <div>
                <h3 class="font-bold text-lg text-black">{{name}}</h3>
                <p class="text-xs text-slate-500">ID: {{id}} • {{slug}}</p>
            </div>
        </div>

        <!-- Section: Matrix -->
        <div class="bg-white p-4 rounded-xl border">
            <h4 class="text-xs font-bold uppercase text-slate-400 mb-4 border-b pb-2">2. Flavor Matrix (0-10)</h4>
            <div class="grid grid-cols-2 gap-4" id="flap-sliders-container">
                <!-- Sliders injected by JS -->
            </div>
        </div>

        <!-- Section: Tags -->
        <div class="form-group">
            <label class="form-label">3. Flavor Tags</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">#</span>
                <input type="text" name="tags" class="input pl-8" placeholder="e.g. vanilla, oak, cinnamon" value="{{tags_value}}">
            </div>
            <p class="text-[10px] text-slate-400 mt-1 italic">Add descriptive keywords, separated by commas.</p>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t mt-2 flap-form-footer">
            <button type="button" class="btn btn-outline" id="flap-cancel">Cancel</button>
            <button type="submit" class="btn btn-primary px-8">{{submit_text}}</button>
        </div>
    </form>
</template>

<!-- ==========================================================================
     FEEDBACK DOMAIN TEMPLATES
     ========================================================================== -->

<!-- Feedback Table Row -->
<template id="tpl-feedback-row">
    <tr class="tr group">
        <td class="td font-medium text-slate-500 text-xs">#{{id}}</td>
        <td class="td">
            <div class="flex items-center gap-1 text-amber-500 font-bold">
                <span>⭐ {{rating}}</span>
            </div>
        </td>
        <td class="td text-slate-500 text-xs italic max-w-xs truncate">{{comment}}</td>
        <td class="td">
            <div class="flex flex-col">
                <span class="font-semibold text-black leading-tight text-sm">{{user_name}}</span>
                <span class="text-[10px] text-slate-400">{{user_email}}</span>
            </div>
        </td>
        <td class="td text-slate-500 text-sm font-medium">{{product_name}}</td>
        <td class="td">
            <span class="badge {{verified_class}} text-[10px]">{{verified_label}}</span>
        </td>
        <td class="td text-slate-500 text-xs">{{created_at}}</td>
        <td class="td">
            <div class="flex gap-2">
                <button class="btn btn-outline btn-sm js-view" data-id="{{id}}">👁️ View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="{{id}}">✏️ Edit</button>
            </div>
        </td>
    </tr>
</template>

<!-- Feedback Form (Create & Edit) -->
<template id="tpl-feedback-form">
    <form class="flex flex-col gap-6" id="fdb-form">
        <div class="grid grid-cols-2 gap-6 bg-slate-50 p-4 rounded-xl border">
            <div class="form-group">
                <label class="form-label field-required">User</label>
                <select name="user_id" class="select" id="fdb-user-select" required>
                    <!-- User options -->
                </select>
            </div>
            <div class="form-group">
                <label class="form-label field-required">Product</label>
                <select name="product_id" class="select" id="fdb-product-select" required>
                    <!-- Product options -->
                </select>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-4">
            <div class="form-group col-span-1">
                <label class="form-label field-required">Rating</label>
                <select name="rating" class="select" required>
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Very Good</option>
                    <option value="3">3 - Good</option>
                    <option value="2">2 - Fair</option>
                    <option value="1">1 - Poor</option>
                </select>
            </div>
            <div class="col-span-3 flex items-center gap-6 pt-6">
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_verified_purchase" class="w-4 h-4" id="fdb-is-verified" {{verified_checked}}>
                    <label for="fdb-is-verified" class="text-sm font-semibold cursor-pointer">Verified Purchase</label>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" class="w-4 h-4" id="fdb-is-active" {{active_checked}}>
                    <label for="fdb-is-active" class="text-sm font-semibold cursor-pointer">Active</label>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Review Comment</label>
            <textarea name="comment" class="textarea text-sm" rows="4" placeholder="Write the review comment here...">{{comment}}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t mt-2 fdb-form-footer">
            <button type="button" class="btn btn-outline" id="fdb-cancel">Cancel</button>
            <button type="submit" class="btn btn-primary px-12">{{submit_text}}</button>
        </div>
    </form>
</template>

<!-- ==========================================================================
     USER DOMAIN TEMPLATES
     ========================================================================== -->

<!-- User Table Row -->
<template id="tpl-user-row">
    <tr class="tr group">
        <td class="td font-medium text-slate-500 text-xs">#{{id}}</td>
        <td class="td">
            <div class="flex flex-col">
                <span class="font-semibold text-black leading-tight">{{name}}</span>
                <span class="text-xs text-slate-400">{{email}}</span>
            </div>
        </td>
        <td class="td text-slate-500 text-sm font-medium">{{phone}}</td>
        <td class="td">
            <span class="badge {{status_class}} text-[10px]">{{status_label}}</span>
        </td>
        <td class="td">
            <span class="badge {{role_class}} text-[10px]">{{role_label}}</span>
        </td>
        <td class="td text-slate-500 text-xs whitespace-nowrap">{{created_at}}</td>
        <td class="td text-slate-500 text-xs whitespace-nowrap">{{last_login}}</td>
        <td class="td">
            <div class="flex gap-2">
                <button class="btn btn-outline btn-sm js-view" data-id="{{id}}">👁️ View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="{{id}}">✏️ Edit</button>
            </div>
        </td>
    </tr>
</template>

<!-- User Form (Create & Edit) -->
<template id="tpl-user-form">
    <form class="flex flex-col gap-6" id="usr-form">
        <div class="grid grid-cols-2 gap-8">
            <!-- Left: Identity -->
            <div class="flex flex-col gap-4">
                <div class="form-group">
                    <label class="form-label field-required">Full Name</label>
                    <input type="text" name="name" class="input" placeholder="e.g. John Doe" required value="{{name}}">
                </div>
                <div class="form-group">
                    <label class="form-label field-required">Email Address</label>
                    <input type="email" name="email" class="input" placeholder="john@example.com" required value="{{email}}">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="input" placeholder="+1..." value="{{phone}}">
                </div>
                <div class="form-group">
                    <label class="form-label {{password_required_class}}">Password</label>
                    <input type="password" name="password" class="input" placeholder="{{password_placeholder}}" {{password_required}}>
                </div>
            </div>

            <!-- Right: Settings & Media -->
            <div class="flex flex-col gap-4">
                <div class="form-group">
                    <label class="form-label">Profile Image</label>
                    <div class="flex items-center gap-4 p-4 border rounded-xl bg-slate-50">
                        <img id="usr-image-preview" src="{{image_url}}" class="thumb-lg border shadow-sm rounded-full" style="display:{{image_display}}" alt="Avatar">
                        <div class="flex-1">
                            <input type="file" id="usr-image-file" class="hidden" accept="image/*">
                            <label for="usr-image-file" class="btn btn-outline w-full cursor-pointer">📷 Upload Avatar</label>
                            <input type="hidden" name="profile_image_url" id="usr-image-hidden" value="{{image_url}}">
                        </div>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-xl border flex flex-col gap-4 mt-2">
                    <h4 class="text-xs font-bold uppercase text-slate-400 border-b pb-2">Permissions & Status</h4>
                    <div class="flex items-center gap-6">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" name="is_admin" class="w-4 h-4" id="usr-is-admin" {{admin_checked}}>
                            <label for="usr-is-admin" class="text-sm font-semibold cursor-pointer">Administrator</label>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="checkbox" name="is_active" class="w-4 h-4" id="usr-is-active" {{active_checked}}>
                            <label for="usr-is-active" class="text-sm font-semibold cursor-pointer">Active</label>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 p-4 rounded-xl border {{info_display}}">
                    <h4 class="text-xs font-bold uppercase text-slate-400 mb-2">Account Timeline</h4>
                    <div class="flex flex-col gap-1 text-[11px] text-slate-500">
                        <div class="flex justify-between"><span>Created:</span><span>{{created_at}}</span></div>
                        <div class="flex justify-between"><span>Last Login:</span><span>{{last_login}}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t mt-2 usr-form-footer">
            <button type="button" class="btn btn-outline" id="usr-cancel">Cancel</button>
            <button type="submit" class="btn btn-primary px-12">{{submit_text}}</button>
        </div>
    </form>
</template>

<!-- ==========================================================================
     STOCK DOMAIN TEMPLATES
     ========================================================================== -->

<!-- Stock Table Row -->
<template id="tpl-stock-row">
    <tr class="tr group">
        <td class="td font-medium text-slate-500 text-xs">#{{id}}</td>
        <td class="td font-semibold text-black leading-tight">{{product_name}}</td>
        <td class="td text-slate-500 text-sm italic">{{warehouse_name}}</td>
        <td class="td text-center font-bold text-black">{{quantity}}</td>
        <td class="td text-center font-medium text-amber-500">{{reserved}}</td>
        <td class="td text-center font-bold {{available_class}}">{{available}}</td>
        <td class="td text-slate-500 text-xs whitespace-nowrap">{{updated_at}}</td>
        <td class="td">
            <div class="flex gap-2">
                <button class="btn btn-outline btn-sm js-view" data-id="{{id}}">👁️ View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="{{id}}">✏️ Adjust</button>
            </div>
        </td>
    </tr>
</template>

<!-- Stock Create Form (Initial Entry) -->
<template id="tpl-stock-create-form">
    <form class="flex flex-col gap-6" id="stk-create-form">
        <div class="bg-amber-50 border border-amber-200 p-4 rounded-xl flex items-start gap-3">
             <span class="text-amber-500 mt-1">⚠️</span>
             <p class="text-xs text-amber-800 leading-tight">
                <strong>New Inventory Entry:</strong> Assign products to a warehouse. Each product/warehouse pair must be unique.
             </p>
        </div>
        
        <div class="grid grid-cols-2 gap-6">
            <div class="form-group">
                <label class="form-label field-required">Target Product</label>
                <select name="product_id" class="select" id="stk-create-product" required>
                    <!-- Options -->
                </select>
            </div>
            <div class="form-group">
                <label class="form-label field-required">Warehouse</label>
                <select name="warehouse_id" class="select" id="stk-create-warehouse" required>
                    <!-- Options -->
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div class="form-group">
                <label class="form-label field-required">Initial Quantity</label>
                <input type="number" name="quantity" class="input" placeholder="0" required min="0">
            </div>
            <div class="form-group">
                <label class="form-label field-required">Creation Reason</label>
                <input type="text" name="reason" class="input" placeholder="e.g. Initial inventory, Received shipment" required>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t mt-2 stk-form-footer">
            <button type="button" class="btn btn-outline" id="stk-create-cancel">Cancel</button>
            <button type="submit" class="btn btn-primary px-12">Create Entry</button>
        </div>
    </form>
</template>

<!-- Stock Adjustment Form (Update Entry) -->
<template id="tpl-stock-adjust-form">
    <form class="flex flex-col gap-6" id="stk-adjust-form">
        <!-- Status Banner -->
        <div class="bg-slate-50 border p-4 rounded-xl grid grid-cols-4 gap-4">
             <div class="text-center"><div class="text-xs text-slate-400 font-bold uppercase">Total</div><div class="text-lg font-bold text-black" id="stk-cur-total">{{total}}</div></div>
             <div class="text-center"><div class="text-xs text-slate-400 font-bold uppercase">Reserved</div><div class="text-lg font-bold text-amber-500" id="stk-cur-res">{{reserved}}</div></div>
             <div class="text-center"><div class="text-xs text-slate-400 font-bold uppercase">Available</div><div class="text-lg font-bold text-green-600" id="stk-cur-avail">{{available}}</div></div>
             <div class="text-center"><div class="text-xs text-slate-400 font-bold uppercase">Orders</div><div class="text-lg font-bold text-slate-800">{{pending_orders}}</div></div>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div class="flex flex-col gap-4">
                <div class="form-group">
                    <label class="form-label field-required">Adjustment Type</label>
                    <select name="adjustment_type" class="select" required id="stk-adj-type">
                        <option value="add">➕ Add to Stock</option>
                        <option value="remove">➖ Remove from Stock</option>
                        <option value="set">🔢 Set Absolute Total</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label field-required">Amount</label>
                    <input type="number" name="adjustment_amount" class="input" placeholder="Quantity" required min="1">
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <div class="form-group">
                    <label class="form-label field-required">Reason Category</label>
                    <select name="reason_category" class="select" required>
                        <option value="restock">Restock from Supplier</option>
                        <option value="damage">Damaged Goods</option>
                        <option value="loss">Loss / Theft</option>
                        <option value="inventory_count">Physical Inventory Count</option>
                        <option value="return">Customer Return</option>
                        <option value="transfer">Inter-warehouse Transfer</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label field-required">Audit Notes</label>
                    <input type="text" name="reason_notes" class="input" placeholder="e.g. Invoice #99, Leakage detected" required>
                </div>
            </div>
        </div>

        <!-- Preview Section -->
        <div id="stk-adj-preview" class="bg-slate-100 p-4 rounded-xl border-dashed border-2 hidden animate-fade">
             <div class="flex justify-between items-center text-sm">
                <span class="text-slate-500 italic">Previewing change...</span>
                <span id="stk-preview-calc" class="font-bold text-black">Target: 0</span>
             </div>
             <div id="stk-preview-status" class="text-[11px] font-bold mt-1"></div>
        </div>

        <div class="flex justify-between items-center pt-6 border-t mt-2">
            <button type="button" class="btn btn-outline text-danger" id="stk-delete-btn">🗑️ Delete Entry</button>
            <div class="flex gap-3">
                <button type="button" class="btn btn-outline" id="stk-adjust-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary px-12">Apply Adjustment</button>
            </div>
        </div>
    </form>
</template>

<!-- ==========================================================================
     RECIPE INGREDIENT DOMAIN TEMPLATES
     ========================================================================== -->

<!-- Recipe Ingredient Row -->
<template id="tpl-recipe-ingredient-row">
    <tr class="tr group">
        <td class="td font-medium text-slate-500 text-xs">#{{id}}</td>
        <td class="td font-semibold text-black leading-tight">{{recipe_name}}</td>
        <td class="td font-medium text-slate-600">{{product_name}}</td>
        <td class="td font-bold text-slate-800">{{quantity}} {{unit}}</td>
        <td class="td">
            <span class="badge {{type_class}} text-[10px]">{{type_label}}</span>
        </td>
        <td class="td">
            <div class="flex gap-2">
                <button class="btn btn-outline btn-sm js-view" data-id="{{id}}">👁️ View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="{{id}}">✏️ Edit</button>
            </div>
        </td>
    </tr>
</template>

<!-- Recipe Ingredient Form (Create & Edit) -->
<template id="tpl-recipe-ingredient-form">
    <form class="flex flex-col gap-6" id="ring-form">
        <!-- Context Header (Edit Mode Only) -->
        <div class="bg-slate-50 border p-4 rounded-xl {{context_display}}">
             <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Editing Ingredient for</div>
             <div class="text-sm font-bold text-black">{{recipe_name}}</div>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div class="flex flex-col gap-4">
                <div class="form-group {{recipe_select_display}}">
                    <label class="form-label field-required">Target Recipe</label>
                    <select name="recipe_id" class="select" id="ring-recipe-select" required>
                        <!-- Options -->
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label field-required">Product</label>
                    <select name="product_id" class="select" id="ring-product-select" required>
                        <!-- Options -->
                    </select>
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label field-required">Quantity</label>
                        <input type="number" name="quantity" class="input" step="0.01" placeholder="e.g. 1.5" required value="{{quantity}}">
                    </div>
                    <div class="form-group">
                        <label class="form-label field-required">Unit</label>
                        <select name="unit" class="select" id="ring-unit-select" required>
                            <option value="oz">oz (ounce)</option>
                            <option value="ml">ml (milliliter)</option>
                            <option value="dash">dash</option>
                            <option value="drop">drop</option>
                            <option value="tsp">tsp (teaspoon)</option>
                            <option value="tbsp">tbsp (tablespoon)</option>
                            <option value="cup">cup</option>
                            <option value="piece">piece</option>
                            <option value="slice">slice</option>
                            <option value="whole">whole</option>
                            <option value="shot">shot</option>
                            <option value="splash">splash</option>
                        </select>
                    </div>
                </div>
                <div class="form-group pt-4">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_optional" class="w-4 h-4" id="ring-is-optional" {{optional_checked}}>
                        <label for="ring-is-optional" class="text-sm font-semibold cursor-pointer">Optional Ingredient</label>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1 ml-7">Can be omitted when making the cocktail</p>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center pt-6 border-t mt-2">
            <button type="button" class="btn btn-outline text-danger {{delete_display}}" id="ring-delete-btn">🗑️ Delete</button>
            <div class="flex gap-3 ml-auto">
                <button type="button" class="btn btn-outline" id="ring-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary px-12">{{submit_text}}</button>
            </div>
        </div>
    </form>
</template>

<!-- ==========================================================================
     USER ADDRESS DOMAIN TEMPLATES
     ========================================================================== -->

<!-- User Address Table Row -->
<template id="tpl-user-address-row">
    <tr class="tr group">
        <td class="td font-medium text-slate-500 text-xs">#{{id}}</td>
        <td class="td">
            <div class="flex flex-col">
                <span class="font-semibold text-black leading-tight">{{user_name}}</span>
                <span class="text-[10px] text-slate-400">#{{user_id}} · {{user_email}}</span>
            </div>
        </td>
        <td class="td">
            <span class="badge badge-secondary text-[10px] uppercase tracking-tighter">{{address_type}}</span>
        </td>
        <td class="td">
            <div class="flex flex-col">
                <span class="text-sm font-medium text-slate-700">{{city}}, {{country}}</span>
            </div>
        </td>
        <td class="td">
            <span class="badge {{default_class}} text-[10px]">{{default_label}}</span>
        </td>
        <td class="td text-slate-500 text-xs whitespace-nowrap">{{created_at}}</td>
        <td class="td">
            <div class="flex gap-2">
                <button class="btn btn-outline btn-sm js-view" data-id="{{id}}">👁️ View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="{{id}}">✏️ Edit</button>
            </div>
        </td>
    </tr>
</template>

<!-- User Address Form (Create & Edit) -->
<template id="tpl-user-address-form">
    <form class="flex flex-col gap-6" id="uadr-form">
        <div class="grid grid-cols-2 gap-8">
            <!-- Left Side: Identity & Meta -->
            <div class="flex flex-col gap-5">
                <div class="bg-white p-4 rounded-xl border flex flex-col gap-3">
                    <h4 class="text-[10px] font-bold uppercase text-slate-400 border-b pb-1">User Assignment</h4>
                    <div class="form-group">
                        <label class="form-label field-required">Owner Account</label>
                        <select name="user_id" class="select" id="uadr-user-select" required>
                            <!-- Options -->
                        </select>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-xl border flex flex-col gap-3">
                    <h4 class="text-[10px] font-bold uppercase text-slate-400 border-b pb-1">Recipient & Contact</h4>
                    <div class="form-group">
                        <label class="form-label">Recipient Name</label>
                        <input type="text" name="recipient_name" class="input" placeholder="e.g. John's Home" value="{{recipient_name}}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Phone</label>
                        <input type="tel" name="phone" class="input" placeholder="+..." value="{{phone}}">
                    </div>
                </div>

                <div class="bg-slate-50 p-4 rounded-xl flex flex-col gap-4">
                    <div class="form-group">
                        <label class="form-label field-required">Address Type</label>
                        <select name="address_type" class="select" id="uadr-type-select" required>
                            <option value="both">Both (Shipping & Billing)</option>
                            <option value="shipping">Shipping Only</option>
                            <option value="billing">Billing Only</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_default" class="w-4 h-4 cursor-pointer" id="uadr-is-default" {{default_checked}}>
                        <label for="uadr-is-default" class="text-sm font-semibold cursor-pointer">Set as Default Address</label>
                    </div>
                </div>
            </div>

            <!-- Right Side: Location Details -->
            <div class="flex flex-col gap-4 bg-white p-6 rounded-xl border shadow-sm">
                <h4 class="text-[10px] font-bold uppercase text-slate-400 border-b pb-2 mb-2">Location Information</h4>
                <div class="form-group">
                    <label class="form-label field-required">Address Line 1</label>
                    <input type="text" name="address_line1" class="input" placeholder="Street, building..." required value="{{address_line1}}">
                </div>
                <div class="form-group">
                    <label class="form-label">Address Line 2</label>
                    <input type="text" name="address_line2" class="input" placeholder="Apt, unit..." value="{{address_line2}}">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label field-required">City</label>
                        <input type="text" name="city" class="input" required value="{{city}}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">State / Province</label>
                        <input type="text" name="state" class="input" value="{{state}}">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label field-required">Postal Code</label>
                        <input type="text" name="postal_code" class="input" required value="{{postal_code}}">
                    </div>
                    <div class="form-group">
                        <label class="form-label field-required">Country</label>
                        <input type="text" name="country" class="input" required value="{{country}}">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center pt-6 border-t mt-2">
            <button type="button" class="btn btn-outline text-danger {{delete_display}}" id="uadr-delete-btn">🗑️ Delete Address</button>
            <div class="flex gap-3 ml-auto">
                <button type="button" class="btn btn-outline" id="uadr-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary px-12">{{submit_text}}</button>
            </div>
        </div>
    </form>
</template>

<!-- ==========================================================================
     USER PREFERENCE DOMAIN TEMPLATES
     ========================================================================== -->

<!-- User Preference Table Row -->
<template id="tpl-user-preference-row">
    <tr class="tr group">
        <td class="td font-medium text-slate-500 text-xs">#{{id}}</td>
        <td class="td font-semibold text-black leading-tight">{{user_name}}</td>
        <td class="td">
            <div class="flex gap-1 flex-wrap">
                <span class="badge badge-secondary text-[9px] px-1" title="Sweetness">Sw:${sweet}</span>
                <span class="badge badge-secondary text-[9px] px-1" title="Bitterness">Bi:${bitter}</span>
                <span class="badge badge-secondary text-[9px] px-1" title="Strength">St:${strength}</span>
                <span class="badge badge-secondary text-[9px] px-1" title="Smokiness">Sm:${smoke}</span>
            </div>
        </td>
        <td class="td">
            <span class="text-xs text-slate-500">{{favorites_summary}}</span>
        </td>
        <td class="td text-slate-500 text-xs whitespace-nowrap">{{created_at}}</td>
        <td class="td">
            <div class="flex gap-2">
                <button class="btn btn-outline btn-sm js-view" data-id="{{id}}">👁️ View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="{{id}}">✏️ Edit</button>
            </div>
        </td>
    </tr>
</template>

<!-- User Preference Form (Edit Only) -->
<template id="tpl-user-preference-form">
    <form class="flex flex-col gap-8" id="upref-form">
        <!-- Context Banner -->
        <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-xl flex items-center gap-3">
             <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-xl">🧬</div>
             <div>
                <div class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest leading-none">Preference Profile</div>
                <div class="text-indigo-900 font-bold text-md leading-tight">{{user_name}}</div>
             </div>
        </div>

        <!-- Flavor Sliders Grid -->
        <div class="flex flex-col gap-4">
            <h4 class="text-xs font-bold uppercase text-slate-400 border-b pb-2">Flavor Profile (0-10)</h4>
            <div class="grid grid-cols-3 gap-6">
                <div class="form-group flex flex-col gap-1">
                    <label class="form-label text-[11px]">Sweetness</label>
                    <select name="preferred_sweetness" class="select select-sm ring-inset" id="upref-sweet"></select>
                </div>
                <div class="form-group flex flex-col gap-1">
                    <label class="form-label text-[11px]">Bitterness</label>
                    <select name="preferred_bitterness" class="select select-sm ring-inset" id="upref-bitter"></select>
                </div>
                <div class="form-group flex flex-col gap-1">
                    <label class="form-label text-[11px]">Strength</label>
                    <select name="preferred_strength" class="select select-sm ring-inset" id="upref-strength"></select>
                </div>
                <div class="form-group flex flex-col gap-1">
                    <label class="form-label text-[11px]">Smokiness</label>
                    <select name="preferred_smokiness" class="select select-sm ring-inset" id="upref-smoke"></select>
                </div>
                <div class="form-group flex flex-col gap-1">
                    <label class="form-label text-[11px]">Fruitiness</label>
                    <select name="preferred_fruitiness" class="select select-sm ring-inset" id="upref-fruit"></select>
                </div>
                <div class="form-group flex flex-col gap-1">
                    <label class="form-label text-[11px]">Spiciness</label>
                    <select name="preferred_spiciness" class="select select-sm ring-inset" id="upref-spice"></select>
                </div>
            </div>
        </div>

        <!-- Favorite Categories Area -->
        <div class="flex flex-col gap-3">
            <h4 class="text-xs font-bold uppercase text-slate-400 border-b pb-2">Favorite Categories</h4>
            <div id="upref-categories-area" class="flex flex-wrap gap-2 p-4 bg-slate-50 border rounded-xl min-h-[100px] max-h-[200px] overflow-y-auto">
                <!-- Checkboxes injected here -->
            </div>
        </div>

        <div class="flex justify-between items-center pt-6 border-t mt-2">
            <button type="button" class="btn btn-outline text-danger" id="upref-delete-btn">🗑️ Reset/Delete</button>
            <div class="flex gap-3 ml-auto">
                <button type="button" class="btn btn-outline" id="upref-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary px-12">Save Preferences</button>
            </div>
        </div>
    </form>
</template>

<!-- ==========================================================================
     COCKTAIL RECIPE DOMAIN TEMPLATES
     ========================================================================== -->

<!-- Cocktail Recipe Table Row -->
<template id="tpl-cocktail-recipe-row">
    <tr class="tr group">
        <td class="td font-medium text-slate-500 text-xs">#{{id}}</td>
        <td class="td font-bold text-black">{{name}}</td>
        <td class="td">
            <div class="flex items-center gap-2">
                <span class="badge {{difficulty_class}} text-[10px] uppercase">{{difficulty}}</span>
                <span class="text-xs text-slate-500">{{preparation_time}}m</span>
            </div>
        </td>
        <td class="td text-slate-600 font-medium">Serves {{serves}}</td>
        <td class="td">
            <span class="badge {{status_class}} text-[10px]">{{status_label}}</span>
        </td>
        <td class="td text-slate-600 text-xs">{{ingredient_count}} ing.</td>
        <td class="td">
            <div class="flex gap-2">
                <button class="btn btn-outline btn-sm js-view" data-id="{{id}}">👁️ View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="{{id}}">✏️ Edit</button>
            </div>
        </td>
    </tr>
</template>

<!-- Cocktail Recipe Form (Create & Edit) -->
<template id="tpl-cocktail-recipe-form">
    <form class="flex flex-col gap-6" id="crec-form" data-id="{{id}}">
        <div class="grid grid-cols-12 gap-8">
            <!-- Left Side: Core Content -->
            <div class="col-span-12 lg:col-span-7 flex flex-col gap-5">
                <div class="form-group">
                    <label class="form-label field-required">Recipe Name</label>
                    <input type="text" name="name" class="input" placeholder="e.g. Royal Gin Fizz" required value="{{name}}">
                </div>
                <div class="form-group">
                    <label class="form-label">Brief Description</label>
                    <textarea name="description" class="textarea" rows="3" placeholder="Describe the cocktail's flavor and history...">{{description}}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Preparation Instructions</label>
                    <textarea name="instructions" class="textarea font-mono text-sm" rows="10" placeholder="1. Add gin and lemon juice...&#10;2. Shake with ice...&#10;3. Strain into glass...">{{instructions}}</textarea>
                </div>
            </div>

            <!-- Right Side: Meta & Media -->
            <div class="col-span-12 lg:col-span-5 flex flex-col gap-5">
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 flex flex-col gap-5">
                    <h4 class="text-[10px] font-bold uppercase text-slate-400 tracking-widest border-b pb-2">Recipe Meta</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label field-required">Difficulty</label>
                            <select name="difficulty" class="select" id="crec-difficulty" required>
                                <option value="easy">Easy</option>
                                <option value="medium">Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label field-required">Prep Time (min)</label>
                            <input type="number" name="preparation_time" class="input" value="{{preparation_time}}" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label field-required">Serves</label>
                            <input type="number" name="serves" class="input" value="{{serves}}" required min="1">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                             <div class="flex items-center gap-3 mt-2">
                                <input type="checkbox" name="is_active" class="w-4 h-4 cursor-pointer" id="crec-active" {{active_checked}}>
                                <label for="crec-active" class="text-sm font-semibold cursor-pointer">Published / Active</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 flex flex-col gap-4">
                    <h4 class="text-[10px] font-bold uppercase text-slate-400 tracking-widest border-b pb-2">Media Asset</h4>
                    <div class="image-upload-wrapper" id="crec-image-wrapper">
                        <!-- Image Input Fragment -->
                        <div class="relative w-full aspect-video bg-slate-100 rounded-xl overflow-hidden group border-2 border-dashed border-slate-200 hover:border-indigo-400 transition-colors">
                            <img src="{{preview_url}}" id="crec-preview" class="w-full h-full object-cover {{preview_display}}" />
                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 cursor-pointer" onclick="document.getElementById('crec-file-input').click()">
                                <span class="text-2xl">📸</span>
                                <span class="text-[10px] font-bold text-slate-500 uppercase">Change Image</span>
                                <input type="file" id="crec-file-input" class="hidden" accept="image/*">
                                <input type="hidden" name="image_url" id="crec-image-url" value="{{image_url}}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center pt-6 border-t mt-2">
            <button type="button" class="btn btn-outline text-danger {{delete_display}}" id="crec-delete-btn">🗑️ Delete Recipe</button>
            <div class="flex gap-3 ml-auto">
                <button type="button" class="btn btn-outline" id="crec-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary px-12">{{submit_text}}</button>
            </div>
        </div>
    </form>
</template>

<!-- ==========================================================================
     PRODUCT RECOGNITION DOMAIN TEMPLATES
     ========================================================================== -->

<!-- Product Recognition Table Row -->
<template id="tpl-product-recognition-row">
    <tr class="tr group">
        <td class="td font-medium text-slate-500 text-xs">#{{id}}</td>
        <td class="td">
            <div class="flex flex-col">
                <span class="font-semibold text-black leading-tight">{{session_id}}</span>
                <span class="text-[10px] text-slate-400">Match: {{product_name}}</span>
            </div>
        </td>
        <td class="td">
            <div class="flex items-center gap-2">
                <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-indigo-500" style="width: {{confidence_percent}}%"></div>
                </div>
                <span class="text-xs font-bold text-slate-700">{{confidence_percent}}%</span>
            </div>
        </td>
        <td class="td">
            <span class="badge {{status_class}} text-[10px]">{{status_label}}</span>
        </td>
        <td class="td text-slate-500 text-xs text-center font-mono">{{processing_time}}ms</td>
        <td class="td text-slate-500 text-xs whitespace-nowrap">{{created_at}}</td>
        <td class="td">
            <div class="flex gap-2">
                <button class="btn btn-outline btn-sm js-view" data-id="{{id}}">👁️ View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="{{id}}">✏️ Edit</button>
            </div>
        </td>
    </tr>
</template>

<!-- Product Recognition Form (Create & Edit) -->
<template id="tpl-product-recognition-form">
    <form class="flex flex-col gap-6" id="prec-form" data-id="{{id}}">
        <div class="grid grid-cols-12 gap-8">
            <!-- Left Side: Context & Mapping -->
            <div class="col-span-12 lg:col-span-6 flex flex-col gap-5">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 flex flex-col gap-4">
                     <h4 class="text-[10px] font-bold uppercase text-slate-400 tracking-widest border-b pb-2">Identification</h4>
                     <div class="form-group">
                        <label class="form-label field-required">Session ID</label>
                        <input type="text" name="session_id" class="input font-mono text-xs" placeholder="session-..." required value="{{session_id}}" {{session_readonly}}>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Associated User</label>
                            <select name="user_id" class="select" id="prec-user-select">
                                <!-- Options -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Matched Product</label>
                            <select name="matched_product_id" class="select" id="prec-product-select">
                                <!-- Options -->
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 flex flex-col gap-4">
                    <h4 class="text-[10px] font-bold uppercase text-slate-400 tracking-widest border-b pb-2">AI Insights</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Confidence Score (%)</label>
                            <input type="number" name="confidence_score" class="input" step="0.01" min="0" max="100" value="{{confidence_score}}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">API Provider</label>
                            <select name="api_provider" class="select" id="prec-provider-select">
                                <option value="">Select Provider</option>
                                <option value="google_vision">Google Vision</option>
                                <option value="clarifai">Clarifai</option>
                                <option value="imagga">Imagga</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="select" id="prec-status-select">
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="manual_review">Manual Review</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Recognized Text / OCR</label>
                    <textarea name="recognized_text" class="textarea text-xs" rows="3" placeholder="Extracted text from image...">{{recognized_text}}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Recognized Labels (comma-separated)</label>
                    <textarea name="recognized_labels_text" class="textarea text-xs" rows="2" placeholder="label1, label2, ...">{{recognized_labels_text}}</textarea>
                </div>
            </div>

            <!-- Right Side: Media Asset -->
            <div class="col-span-12 lg:col-span-6 flex flex-col gap-5">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 flex flex-col gap-4 h-full">
                    <h4 class="text-[10px] font-bold uppercase text-slate-400 tracking-widest border-b pb-2">Recognition Source</h4>
                    <div class="image-upload-wrapper flex-grow flex flex-col" id="prec-image-wrapper">
                        <div class="relative w-full aspect-square lg:aspect-auto lg:flex-grow bg-slate-100 rounded-xl overflow-hidden group border-2 border-dashed border-slate-200 hover:border-indigo-400 transition-colors">
                            <img src="{{preview_url}}" id="prec-preview" class="w-full h-full object-contain {{preview_display}}" />
                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 cursor-pointer bg-black/0 group-hover:bg-black/5 transition-all" onclick="document.getElementById('prec-file-input').click()">
                                <span class="text-2xl drop-shadow-md">🖼️</span>
                                <span class="text-[10px] font-bold text-slate-500 uppercase">Replace Asset</span>
                                <input type="file" id="prec-file-input" class="hidden" accept="image/*">
                                <input type="hidden" name="image_url" id="prec-image-url" value="{{image_url}}">
                            </div>
                        </div>
                        <p class="text-[9px] text-slate-400 mt-3 italic text-center">Original resolution is preserved for AI analysis integrity.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center pt-6 border-t mt-2">
            <button type="button" class="btn btn-outline text-danger {{delete_display}}" id="prec-delete-btn">🗑️ Delete Entry</button>
            <div class="flex gap-3 ml-auto">
                <button type="button" class="btn btn-outline" id="prec-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary px-12">{{submit_text}}</button>
            </div>
        </div>
    </form>
</template>

<!-- ==========================================================================
     ORDER DOMAIN TEMPLATES
     ========================================================================== -->

<!-- Order Table Row -->
<template id="tpl-order-row">
    <tr class="tr group">
        <td class="td font-medium text-slate-500 text-xs">#{{id}}</td>
        <td class="td">
            <div class="flex flex-col">
                <span class="font-bold text-black leading-tight">{{order_number}}</span>
                <span class="text-[10px] text-slate-400">Items: {{item_count}}</span>
            </div>
        </td>
        <td class="td">
            <span class="badge {{status_class}} text-[10px] uppercase font-bold tracking-wider">{{status}}</span>
        </td>
        <td class="td font-bold text-black">${{total}}</td>
        <td class="td">
            <div class="flex flex-col">
                <span class="text-xs font-semibold text-slate-700">{{user_name}}</span>
                <span class="text-[9px] text-slate-400 truncate max-w-[120px]">{{user_email}}</span>
            </div>
        </td>
        <td class="td text-slate-500 text-xs whitespace-nowrap">{{created_at}}</td>
        <td class="td">
            <div class="flex gap-2">
                <button class="btn btn-outline btn-sm js-view" data-id="{{id}}">👁️ View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="{{id}}">✏️ Edit</button>
            </div>
        </td>
    </tr>
</template>

<!-- Order Form (Create & Edit) -->
<template id="tpl-order-form">
    <form class="flex flex-col gap-6" id="ord-form" data-id="{{id}}">
        <div class="grid grid-cols-12 gap-8">
            <!-- Left Side: Basic Info -->
            <div class="col-span-12 lg:col-span-7 flex flex-col gap-5">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 flex flex-col gap-4">
                     <h4 class="text-[10px] font-bold uppercase text-slate-400 tracking-widest border-b pb-2">Core Assignment</h4>
                     <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label field-required">Customer</label>
                            <select name="user_id" class="select select-sm ring-inset" id="ord-user-select" required></select>
                        </div>
                        <div class="form-group">
                            <label class="form-label field-required">Order Reference #</label>
                            <input type="text" name="order_number" class="input input-sm" placeholder="ORD-2024-XXXX" required value="{{order_number}}">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label field-required">Status</label>
                            <select name="status" class="select select-sm ring-inset" id="ord-status-select" required>
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label field-required">Total Amount (Cents)</label>
                            <input type="number" name="total_cents" class="input input-sm" placeholder="e.g. 5000 for $50.00" required value="{{total_cents}}">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div class="form-group">
                            <label class="form-label">Shipping Address</label>
                            <select name="shipping_address_id" class="select select-sm ring-inset" id="ord-shipping-select">
                                <option value="">Identify Recipient Address...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Billing Address</label>
                            <select name="billing_address_id" class="select select-sm ring-inset" id="ord-billing-select">
                                <option value="">Identify Billing Protocol...</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-50/50 p-5 rounded-2xl border border-indigo-100 flex flex-col gap-4 {{create_only}}">
                    <h4 class="text-[10px] font-bold uppercase text-indigo-400 tracking-widest border-b border-indigo-100 pb-2 italic">Creation Context</h4>
                    <div class="form-group">
                        <label class="form-label field-required text-indigo-900">Source Cart</label>
                        <select name="cart_id" class="select select-sm ring-inset bg-white border-indigo-200" id="ord-cart-select" {{cart_required}}></select>
                        <p class="text-[10px] text-indigo-400 mt-1 italic">Note: Cart items will be cloned to this order upon creation.</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Internal Admin Notes</label>
                    <textarea name="notes" class="textarea text-sm" rows="5" placeholder="Private internal notes regarding this order processing...">{{notes}}</textarea>
                </div>
            </div>

            <!-- Right Side: Metadata / Read-only Refs -->
            <div class="col-span-12 lg:col-span-5 flex flex-col gap-5">
                <div class="bg-slate-900 text-slate-300 p-6 rounded-2xl border border-slate-800 shadow-xl">
                    <h4 class="text-[10px] font-bold uppercase text-slate-500 tracking-widest border-b border-slate-800 pb-2 mb-4 text-center">Transaction Summary</h4>
                    <div class="flex flex-col gap-4">
                        <div class="flex justify-between items-end">
                            <span class="text-xs">Subtotal (calculated)</span>
                            <span class="font-mono text-white">$0.00</span>
                        </div>
                        <div class="flex justify-between items-end">
                            <span class="text-xs">Adjustment Field</span>
                            <span class="font-mono text-white">$0.00</span>
                        </div>
                        <div class="border-t border-slate-800 pt-3 flex justify-between items-end">
                            <span class="text-sm font-bold text-white uppercase tracking-wider">Total</span>
                            <span class="text-2xl font-black text-indigo-400 font-mono">$0.00</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-5 rounded-2xl border border-slate-200 flex flex-col gap-4 {{edit_only}}">
                    <h4 class="text-[10px] font-bold uppercase text-slate-400 tracking-widest border-b pb-2">Record Attributes</h4>
                    <div class="flex flex-col gap-2 text-xs">
                        <div class="flex justify-between"><span class="text-slate-500">System ID</span><span class="font-bold">#{{id}}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Established</span><span>{{created_at}}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Last Payment</span><span>{{paid_at}}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center pt-6 border-t mt-2">
            <button type="button" class="btn btn-outline text-danger {{delete_display}}" id="ord-delete-btn">🗑️ Void Order</button>
            <div class="flex gap-3 ml-auto">
                <button type="button" class="btn btn-outline" id="ord-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary px-12">{{submit_text}}</button>
            </div>
        </div>
    </form>
</template>

<!-- ==========================================================================
     ORDER ITEM DOMAIN TEMPLATES
     ========================================================================== -->

<!-- Order Item Table Row -->
<template id="tpl-order-item-row">
    <tr class="tr group">
        <td class="td font-medium text-slate-500 text-xs">#{{id}}</td>
        <td class="td">
            <div class="flex flex-col">
                <span class="font-bold text-black leading-tight">{{product_name}}</span>
                <span class="text-[10px] text-indigo-500 font-mono">Order #{{order_id}}</span>
            </div>
        </td>
        <td class="td text-xs text-slate-600 font-medium">{{user_name}}</td>
        <td class="td text-center font-mono font-bold text-black text-sm">x{{quantity}}</td>
        <td class="td text-right font-mono text-xs text-slate-500">${{unit_price}}</td>
        <td class="td text-right font-mono font-bold text-black text-sm">${{subtotal}}</td>
        <td class="td">
            <span class="badge {{status_class}} text-[9px] uppercase font-bold">{{status}}</span>
        </td>
        <td class="td text-slate-400 text-[10px] whitespace-nowrap">{{created_at}}</td>
        <td class="td">
            <div class="flex gap-2">
                <button class="btn btn-outline btn-sm js-view" data-id="{{id}}">👁️ View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="{{id}}">✏️ Edit</button>
            </div>
        </td>
    </tr>
</template>

<!-- Order Item Form (Edit Only) -->
<template id="tpl-order-item-form">
    <form class="flex flex-col gap-6" id="oit-form" data-id="{{id}}">
        <div class="bg-amber-50 border border-amber-200 p-4 rounded-2xl flex gap-4 items-start">
            <div class="text-2xl mt-0.5">⚠️</div>
            <div>
                <h4 class="text-xs font-bold text-amber-900 uppercase tracking-tight">Transactional Correction Protocol</h4>
                <p class="text-xs text-amber-700 leading-relaxed mt-1">Adjusting line items directly impacts order totals and inventory synchronization. Proceed with caution for discrepancy resolution only.</p>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-8">
            <!-- Left Side: Context -->
            <div class="col-span-12 lg:col-span-6 flex flex-col gap-5">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 flex flex-col gap-4">
                     <h4 class="text-[10px] font-bold uppercase text-slate-400 tracking-widest border-b pb-2">Line Item Identity</h4>
                     <div class="flex flex-col gap-3">
                        <div class="flex justify-between border-b border-slate-50 pb-2">
                            <span class="text-xs text-slate-500">Order Ref</span>
                            <span class="text-xs font-bold text-black">#{{order_id}}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-50 pb-2">
                            <span class="text-xs text-slate-500">Product (Snapshot)</span>
                            <span class="text-xs font-bold text-black truncate max-w-[150px]">{{product_name}}</span>
                        </div>
                         <div class="flex justify-between border-b border-slate-50 pb-2">
                            <span class="text-xs text-slate-500">Fixed Unit Price</span>
                            <span class="text-xs font-mono font-bold text-black">${{unit_price}}</span>
                        </div>
                     </div>
                </div>
            </div>

            <!-- Right Side: Edit Fields -->
            <div class="col-span-12 lg:col-span-6 flex flex-col gap-5">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col gap-4">
                    <h4 class="text-[10px] font-bold uppercase text-indigo-400 tracking-widest border-b border-indigo-50 pb-2">Adjustment Parameters</h4>
                    <div class="form-group">
                        <label class="form-label field-required">Adjustment Quantity</label>
                        <input type="number" name="quantity" class="input input-sm" min="1" required value="{{quantity}}">
                        <p class="text-[9px] text-slate-400 mt-1 italic">Historical subtotal: ${{subtotal}}</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Warehouse Fulfillment ID</label>
                        <input type="number" name="warehouse_id" class="input input-sm font-mono" placeholder="Internal ID..." value="{{warehouse_id}}">
                        <p class="text-[9px] text-slate-400 mt-1">Assigned for distribution lookup.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end items-center pt-6 border-t mt-2 gap-3">
            <button type="button" class="btn btn-outline" id="oit-cancel">Cancel</button>
            <button type="submit" class="btn btn-primary px-12" id="oit-submit">Save Adjustments</button>
        </div>
    </form>
</template>

<!-- ==========================================================================
     CART DOMAIN TEMPLATES
     ========================================================================== -->

<!-- Cart Table Row -->
<template id="tpl-cart-row">
    <tr class="tr group">
        <td class="td font-medium text-slate-500 text-xs">#{{id}}</td>
        <td class="td">
            <div class="flex flex-col">
                <span class="font-mono text-black text-xs leading-tight">{{session_preview}}...</span>
                <span class="text-[9px] text-slate-400">Total Units: {{item_count}}</span>
            </div>
        </td>
        <td class="td">
            <span class="badge {{status_class}} text-[10px] uppercase font-bold tracking-wider">{{status}}</span>
        </td>
        <td class="td font-bold text-black">${{total}}</td>
        <td class="td">
            <div class="flex flex-col">
                <span class="text-xs font-semibold text-slate-700">{{user_name}}</span>
                <span class="text-[9px] text-slate-400">{{user_email}}</span>
            </div>
        </td>
        <td class="td text-slate-500 text-xs whitespace-nowrap text-center">
            <div class="flex flex-col">
                <span>{{created_at}}</span>
                <span class="text-[9px] text-slate-300 italic">ID: {{user_id}}</span>
            </div>
        </td>
        <td class="td">
            <div class="flex gap-2">
                <button class="btn btn-outline btn-sm js-view" data-id="{{id}}">👁️ View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="{{id}}">✏️ Edit</button>
            </div>
        </td>
    </tr>
</template>

<!-- Cart Form (Create & Edit) -->
<template id="tpl-cart-form">
    <form class="flex flex-col gap-6" id="crt-form" data-id="{{id}}">
        <div class="grid grid-cols-12 gap-8">
            <!-- Left Side -->
            <div class="col-span-12 lg:col-span-7 flex flex-col gap-6">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 flex flex-col gap-4">
                     <h4 class="text-[10px] font-bold uppercase text-slate-400 tracking-widest border-b pb-2">Consumer Relation</h4>
                     <div class="form-group {{create_only}}">
                        <label class="form-label field-required">Assign User</label>
                        <div class="searchable-select-container" id="crt-user-search-wrapper">
                            <!-- SearchableSelect injected here -->
                        </div>
                    </div>
                    <div class="flex flex-col gap-1 {{edit_only}}">
                        <span class="text-[9px] font-bold text-slate-400 uppercase">Assigned Recipient</span>
                        <div class="font-bold text-black">{{user_name}}</div>
                        <div class="text-xs text-slate-500 underline">{{user_email}}</div>
                    </div>
                </div>

                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 flex flex-col gap-4">
                    <h4 class="text-[10px] font-bold uppercase text-slate-400 tracking-widest border-b pb-2">Lifecycle Management</h4>
                    <div class="form-group">
                        <label class="form-label field-required">Cart Status</label>
                        <select name="status" class="select select-sm" required id="crt-status-select">
                            <option value="active">Active (Shopping)</option>
                            <option value="converted">Converted (Purchased)</option>
                            <option value="abandoned">Abandoned (Static)</option>
                            <option value="expired">Expired (Inactive)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Right Side -->
            <div class="col-span-12 lg:col-span-5 flex flex-col gap-6">
                 <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl text-white shadow-xl">
                    <h4 class="text-[9px] font-bold uppercase text-slate-500 tracking-widest border-b border-slate-800 pb-2 text-center mb-4">Cart Diagnostics</h4>
                    <div class="flex flex-col gap-3 font-mono text-xs">
                        <div class="flex justify-between"><span>Value Snapshot</span><span class="text-indigo-400">${{total}}</span></div>
                        <div class="flex justify-between"><span>Unit Manifest</span><span class="text-indigo-400">{{item_count}} items</span></div>
                        <div class="flex justify-between border-t border-slate-800 pt-3"><span>Established</span><span>{{created_at}}</span></div>
                        <div class="flex justify-between"><span>Last Handshake</span><span>{{updated_at}}</span></div>
                    </div>
                </div>

                <div class="form-group bg-white p-5 rounded-2xl border shadow-sm">
                    <label class="form-label">Session Identity (Immutable)</label>
                    <input type="text" value="{{session_id}}" class="input input-sm font-mono text-[10px] bg-slate-50" readonly>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center pt-6 border-t mt-2">
            <button type="button" class="btn btn-outline text-danger {{delete_display}}" id="crt-delete-btn">🗑️ Delete Cart</button>
            <div class="flex gap-3 ml-auto">
                <button type="button" class="btn btn-outline" id="crt-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary px-12">{{submit_text}}</button>
            </div>
        </div>
    </form>
</template>

<!-- ==========================================================================
     CART ITEM DOMAIN TEMPLATES
     ========================================================================== -->

<!-- Cart Item Table Row -->
<template id="tpl-cart-item-row">
    <tr class="tr group">
        <td class="td font-medium text-slate-500 text-xs">#{{id}}</td>
        <td class="td">
            <div class="flex flex-col">
                <span class="font-bold text-black leading-tight">{{product_name}}</span>
                <span class="text-[10px] text-indigo-500 font-mono">Cart #{{cart_id}}</span>
            </div>
        </td>
        <td class="td text-xs text-slate-600 font-medium">{{user_name}}</td>
        <td class="td text-center font-mono font-bold text-black text-sm">x{{quantity}}</td>
        <td class="td text-right font-mono text-xs text-slate-500">${{unit_price}}</td>
        <td class="td text-right font-mono font-bold text-black text-sm">${{subtotal}}</td>
        <td class="td text-slate-400 text-[10px] whitespace-nowrap">{{created_at}}</td>
        <td class="td">
            <div class="flex gap-2">
                <button class="btn btn-outline btn-sm js-view" data-id="{{id}}">👁️ View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="{{id}}">✏️ Edit</button>
            </div>
        </td>
    </tr>
</template>

<!-- Cart Item Form (Edit Only) -->
<template id="tpl-cart-item-form">
    <form class="flex flex-col gap-6" id="cit-form" data-id="{{id}}">
        <div class="grid grid-cols-12 gap-8">
            <!-- Left Side -->
            <div class="col-span-12 lg:col-span-6 flex flex-col gap-5">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 flex flex-col gap-4">
                     <h4 class="text-[10px] font-bold uppercase text-slate-400 tracking-widest border-b pb-2">Interest Context</h4>
                     <div class="flex flex-col gap-4 mt-2 {{create_only}}">
                        <div class="form-group">
                            <label class="form-label field-required">Target Cart</label>
                            <select name="cart_id" class="select select-sm" id="cit-cart-select" {{create_required}}></select>
                        </div>
                        <div class="form-group">
                            <label class="form-label field-required">Product Trace</label>
                            <select name="product_id" class="select select-sm" id="cit-product-select" {{create_required}}></select>
                        </div>
                     </div>
                     <div class="flex flex-col gap-3 {{edit_only}}">
                        <div class="flex justify-between border-b border-slate-50 pb-2">
                            <span class="text-xs text-slate-500">Cart Link</span>
                            <span class="text-xs font-bold text-black font-mono">#{{cart_id}}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-50 pb-2">
                            <span class="text-xs text-slate-500">Atomic Product</span>
                            <span class="text-xs font-bold text-black truncate max-w-[150px]">{{product_name}}</span>
                        </div>
                         <div class="flex justify-between border-b border-slate-50 pb-2">
                            <span class="text-xs text-slate-500">Price at Entry</span>
                            <span class="text-xs font-mono font-bold text-black">${{unit_price}}</span>
                        </div>
                     </div>
                </div>
            </div>

            <!-- Right Side -->
            <div class="col-span-12 lg:col-span-6 flex flex-col gap-5">
                <div class="bg-indigo-50/50 p-5 rounded-2xl border border-indigo-100 shadow-sm flex flex-col gap-4">
                    <h4 class="text-[10px] font-bold uppercase text-indigo-400 tracking-widest border-b border-indigo-50 pb-2">Quantity Allocation</h4>
                    <div class="form-group">
                        <label class="form-label field-required">Desired Units</label>
                        <input type="number" name="quantity" class="input input-sm font-mono text-center" min="1" required value="{{quantity}}">
                        <p class="text-[9px] text-slate-400 mt-1 italic">Calculated subtotal: ${{subtotal}}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center pt-6 border-t mt-2">
            <button type="button" class="btn btn-outline text-danger" id="cit-delete-btn">🗑️ Remove Item</button>
            <div class="flex gap-3 ml-auto">
                <button type="button" class="btn btn-outline" id="cit-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary px-12">Commit Changes</button>
            </div>
        </div>
    </form>
</template>

<!-- ==========================================================================
     PAYMENT DOMAIN TEMPLATES
     ========================================================================== -->

<!-- Payment Table Row -->
<template id="tpl-payment-row">
    <tr class="tr group">
        <td class="td font-medium text-slate-500 text-xs">#{{id}}</td>
        <td class="td">
            <div class="flex flex-col">
                <span class="font-bold text-black leading-tight">Order #{{order_number}}</span>
                <span class="text-[10px] text-slate-400 font-mono">System ID: {{order_id}}</span>
            </div>
        </td>
        <td class="td">
            <div class="flex flex-col">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-tighter">{{gateway}}</span>
                <span class="text-[9px] text-slate-400 truncate max-w-[80px]">{{transaction_id}}</span>
            </div>
        </td>
        <td class="td">
            <span class="badge {{status_class}} text-[10px] uppercase font-bold tracking-wider">{{status}}</span>
        </td>
        <td class="td font-bold text-black font-mono text-sm">${{amount}}</td>
        <td class="td text-slate-500 text-xs whitespace-nowrap text-center">{{created_at}}</td>
        <td class="td">
            <div class="flex gap-2">
                <button class="btn btn-outline btn-sm js-view" data-id="{{id}}">👁️ View</button>
                <button class="btn btn-primary btn-sm js-edit" data-id="{{id}}">✏️ Edit</button>
            </div>
        </td>
    </tr>
</template>

<!-- Payment Form (Create & Edit) -->
<template id="tpl-payment-form">
    <form class="flex flex-col gap-6" id="pay-form" data-id="{{id}}">
        <div class="grid grid-cols-12 gap-8">
            <!-- Left Side: Transactional -->
            <div class="col-span-12 lg:col-span-6 flex flex-col gap-5">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 flex flex-col gap-4">
                     <h4 class="text-[10px] font-bold uppercase text-slate-400 tracking-widest border-b pb-2">Settlement Pipeline</h4>
                     <div class="form-group {{create_only}}">
                        <label class="form-label field-required">Parent Order</label>
                        <select name="order_id" class="select select-sm" id="pay-order-select" required></select>
                    </div>
                    <div class="flex flex-col gap-1 {{edit_only}}">
                        <span class="text-[9px] font-bold text-slate-400 uppercase">Assigned Transaction</span>
                        <div class="font-bold text-black">Order #{{order_number}}</div>
                        <div class="text-[10px] text-slate-400 font-mono">Internal ID: {{order_id}}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label field-required">Amount (Cents)</label>
                            <input type="number" name="amount_cents" class="input input-sm font-mono" required value="{{amount_cents}}">
                        </div>
                        <div class="form-group">
                            <label class="form-label field-required">Currency</label>
                            <input type="text" name="currency" class="input input-sm uppercase font-bold" required value="{{currency}}">
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-50/50 p-5 rounded-2xl border border-indigo-100 flex flex-col gap-4">
                    <h4 class="text-[10px] font-bold uppercase text-indigo-400 tracking-widest border-b border-indigo-100 pb-2">Provider Authority</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label field-required">Gateway Entity</label>
                            <select name="gateway" class="select select-sm bg-white border-indigo-200" required id="pay-gateway-select">
                                <option value="stripe">Stripe</option>
                                <option value="paypal">PayPal</option>
                                <option value="manual">Manual/Internal</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label field-required">Protocol Status</label>
                            <select name="status" class="select select-sm bg-white border-indigo-200" required id="pay-status-select">
                                <option value="pending">Pending</option>
                                <option value="captured">Captured</option>
                                <option value="refunded">Refunded</option>
                                <option value="voided">Voided</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: References & Metadata -->
            <div class="col-span-12 lg:col-span-6 flex flex-col gap-5">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 flex flex-col gap-4">
                    <h4 class="text-[10px] font-bold uppercase text-slate-400 tracking-widest border-b pb-2">Identifier Strings</h4>
                    <div class="form-group">
                        <label class="form-label">Gateway Order ID</label>
                        <input type="text" name="gateway_order_id" class="input input-sm font-mono text-[10px]" value="{{gateway_order_id}}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Transaction Reference</label>
                        <input type="text" name="transaction_id" class="input input-sm font-mono text-[10px]" value="{{transaction_id}}">
                    </div>
                </div>

                <div class="form-group bg-slate-900 p-5 rounded-2xl border shadow-xl flex flex-col gap-2">
                    <label class="text-[10px] font-bold uppercase text-slate-500 tracking-widest border-b border-slate-800 pb-1 mb-1">Raw Forensic Payload (JSON)</label>
                    <textarea name="payload" rows="4" class="textarea text-[10px] font-mono bg-slate-800 text-indigo-300 border-slate-700 focus:border-indigo-500">{{payload_json}}</textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center pt-6 border-t mt-2">
            <button type="button" class="btn btn-outline text-danger {{delete_display}}" id="pay-delete-btn">🗑️ Delete Record</button>
            <div class="flex gap-3 ml-auto">
                <button type="button" class="btn btn-outline" id="pay-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary px-12">{{submit_text}}</button>
            </div>
        </div>
    </form>
</template>
