package com.cefc.volunteers.data.model

import com.google.gson.annotations.SerializedName

data class GalleryImage(
    @SerializedName("id") val id: Int,
    @SerializedName("title") val title: String,
    @SerializedName("description") val description: String?,
    @SerializedName("image_url") val imageUrl: String,
    @SerializedName("thumbnail_url") val thumbnailUrl: String?,
    @SerializedName("category") val category: String,
    @SerializedName("is_featured") val isFeatured: Boolean,
    @SerializedName("views") val views: Int,
    @SerializedName("created_at") val createdAt: String
)

data class GalleryResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("images") val images: List<GalleryImage>?,
    @SerializedName("total") val total: Int?,
    @SerializedName("message") val message: String?
)

data class GalleryUploadResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("message") val message: String?,
    @SerializedName("id") val id: Int?,
    @SerializedName("image_url") val imageUrl: String?,
    @SerializedName("thumbnail_url") val thumbnailUrl: String?
)
