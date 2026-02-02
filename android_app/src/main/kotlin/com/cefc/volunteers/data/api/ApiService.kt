package com.cefc.volunteers.data.api

import com.cefc.volunteers.data.model.*
import okhttp3.MultipartBody
import okhttp3.RequestBody
import retrofit2.Response
import retrofit2.http.*

interface ApiService {

    // Authentication
    @POST("volunteers_api.php")
    @FormUrlEncoded
    suspend fun login(
        @Field("action") action: String = "login",
        @Field("email") email: String,
        @Field("password") password: String,
        @Field("csrf_token") csrfToken: String
    ): Response<LoginResponse>

    @POST("volunteers_api.php")
    @FormUrlEncoded
    suspend fun signup(
        @Field("action") action: String = "signup",
        @Field("name") name: String,
        @Field("email") email: String,
        @Field("phone") phone: String,
        @Field("ministry") ministry: String,
        @Field("password") password: String,
        @Field("csrf_token") csrfToken: String
    ): Response<SignupResponse>

    @POST("volunteers_api.php")
    @FormUrlEncoded
    suspend fun verifyOtp(
        @Field("action") action: String = "verify_otp",
        @Field("email") email: String,
        @Field("otp") otp: String,
        @Field("csrf_token") csrfToken: String
    ): Response<VerifyResponse>

    @POST("volunteers_api.php")
    @FormUrlEncoded
    suspend fun logout(
        @Field("action") action: String = "logout",
        @Field("csrf_token") csrfToken: String
    ): Response<BaseResponse>

    // Sermon Management
    @GET("sermons_api.php")
    suspend fun getSermons(): Response<SermonsResponse>

    @POST("sermons_api.php")
    @FormUrlEncoded
    suspend fun getSermonsByMinistry(
        @Field("action") action: String = "get_by_ministry",
        @Field("ministry") ministry: String,
        @Field("csrf_token") csrfToken: String
    ): Response<SermonsResponse>

    @GET("sermons_api.php")
    suspend fun getSermonDetail(
        @Query("id") id: Int
    ): Response<SermonDetailResponse>

    @Multipart
    @POST("sermons_api.php")
    suspend fun uploadSermon(
        @Part("action") action: RequestBody,
        @Part("title") title: RequestBody,
        @Part("speaker") speaker: RequestBody,
        @Part("ministry") ministry: RequestBody,
        @Part("date") date: RequestBody,
        @Part("description") description: RequestBody,
        @Part("csrf_token") csrfToken: RequestBody,
        @Part file: MultipartBody.Part,
        @Part thumbnail: MultipartBody.Part? = null
    ): Response<UploadResponse>

    @POST("sermons_api.php")
    @FormUrlEncoded
    suspend fun deleteSermon(
        @Field("action") action: String = "delete",
        @Field("id") id: Int,
        @Field("csrf_token") csrfToken: String
    ): Response<BaseResponse>

    @POST("sermons_api.php")
    @FormUrlEncoded
    suspend fun updateSermon(
        @Field("action") action: String = "update",
        @Field("id") id: Int,
        @Field("title") title: String,
        @Field("speaker") speaker: String,
        @Field("description") description: String,
        @Field("csrf_token") csrfToken: String
    ): Response<BaseResponse>

    // Volunteer Commitment
    @Multipart
    @POST("volunteers_api.php")
    suspend fun uploadCommitmentForm(
        @Part("action") action: RequestBody,
        @Part("csrf_token") csrfToken: RequestBody,
        @Part file: MultipartBody.Part
    ): Response<UploadResponse>

    // Messages
    @POST("volunteers_api.php")
    @FormUrlEncoded
    suspend fun submitMessage(
        @Field("action") action: String = "submit_message",
        @Field("msg_type") msgType: String,
        @Field("message") message: String,
        @Field("csrf_token") csrfToken: String
    ): Response<BaseResponse>
    
    // Gallery Management
    @GET("gallery_api.php")
    suspend fun getGalleryImages(
        @Query("category") category: String? = null,
        @Query("limit") limit: Int = 50,
        @Query("offset") offset: Int = 0
    ): Response<GalleryResponse>
    
    @GET("gallery_api.php")
    suspend fun getGalleryImageDetail(
        @Query("id") id: Int
    ): Response<GalleryResponse>
    
    @Multipart
    @POST("gallery_api.php")
    suspend fun uploadGalleryImage(
        @Part("action") action: RequestBody,
        @Part("title") title: RequestBody,
        @Part("description") description: RequestBody,
        @Part("category") category: RequestBody,
        @Part("is_featured") isFeatured: RequestBody,
        @Part("uploaded_by") uploadedBy: RequestBody,
        @Part("csrf_token") csrfToken: RequestBody,
        @Part image: MultipartBody.Part
    ): Response<GalleryUploadResponse>
    
    @POST("gallery_api.php")
    @FormUrlEncoded
    suspend fun deleteGalleryImage(
        @Field("action") action: String = "delete",
        @Field("id") id: Int,
        @Field("csrf_token") csrfToken: String
    ): Response<BaseResponse>
    
    @GET("gallery_api.php")
    suspend fun getGalleryCategories(
        @Query("action") action: String = "categories"
    ): Response<BaseResponse>
}
