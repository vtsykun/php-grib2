#include <stdio.h>

struct __sFile
{
    int unused;
};

typedef struct __sFILE FILE;

extern FILE *fopen (const char *__restrict __filename, const char *__restrict __modes);
int fclose(FILE* stream);

typedef struct grib_context grib_context;
typedef struct grib_handle grib_handle;
typedef struct grib_handle codes_handle;

grib_context* grib_context_get_default();
void grib_context_delete(grib_context* c);

grib_handle* grib_handle_new_from_file(grib_context* c, FILE* f, int* error);

void grib_handle_delete(grib_handle* h);
char* grib_get_string(grib_handle* h, const char* key, int* error);
double grib_get_double(grib_handle* h, const char* key, int* error);
int grib_get_size(grib_handle* h, const char* key, int* error);
int grib_get_values(grib_handle* h, const char* key, double* values, size_t* size);

int codes_get_size(const codes_handle* h, const char* key, size_t* size);
int codes_get_double_array(const codes_handle* h, const char* key, double* vals, size_t* length);
int codes_get_float_array(const codes_handle* h, const char* key, float* vals, size_t* length);
int codes_handle_delete(codes_handle* h);
