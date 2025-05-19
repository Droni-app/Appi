export {}
declare global {
  export interface AuthUser {
    id: string
    name: string
    email: string
    avatar: string
    created_at: string
    updated_at: string
  }

  export interface AuthMe {
    id: string
    name: string
    email: string
    avatar: string
    created_at: string
    updated_at: string
    enrollments: Enrollment[]
  }

  export interface Enrollment {
    id: string
    user_id: string
    site_id: string
    role: string
    created_at: string
    updated_at: string
    site: Site
  }

  export interface Site {
    id: string
    secret: any
    name: string
    domain: string
    description: string
    logo: any
    icon: any
    provider: string
    provider_client_id: string
    provider_client_secret: any
    created_at: string
    updated_at: string
  }


}
