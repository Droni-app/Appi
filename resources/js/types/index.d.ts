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

  export interface Pagination<T extends Iterable<unknown>> {
    data: T
    current_page?: number
    from?: number
    last_page?: number
    per_page?: number
    to?: number
    total?: number
  }

  export interface ContentPost {
    id: number
    user_id: string
    site_id: string
    category_id: number
    slug: string
    name: string
    description: string
    picture: string
    content: string
    format: string
    active: number
    created_at: string
    updated_at: string
    category: ContentCategory
    user: User
    attrs?: ContentAttr[]
  }

  export interface ContentAttr {
    id: number
    post_id: number
    name: string
    type: string
    value: string
  }

  export interface Category {
    id: number
    site_id: string
    slug: string
    name: string
    description: string
    picture: string
    created_at: string
    updated_at: string
  }

  export interface User {
    id: string
    name: string
    email: string
    avatar: string
    created_at: string
    updated_at: string
  }

  export interface ContentAttachment {
    user_id: string
    site_id: string
    name: string
    path: string
    provider: string
    size: number
    extension: string
    mime_type: string
    id: string
    updated_at: string
    created_at: string
    url: string
  }

  export interface ContentCategory {
    id: number
    site_id: string
    slug: string
    name: string
    description: string
    picture: string
    created_at: string
    updated_at: string
  }
  export interface SocialComment {
    id: number
    site_id: string
    commentable_type: string
    commentable_id: string
    user_id: string
    parent_id?: string
    content: string
    approved_at: string
    created_at: string
    updated_at: string
    user: User
    parent?: SocialComment
    children: SocialComment[]
  }
  export interface LearnCourse {
    id: number
    site_id: string
    user_id: string
    category: any
    slug: string
    name: string
    icon: any
    picture: any
    video: any
    description: string
    open: number
    created_at: string
    updated_at: string
  }
  export interface LearnLesson {
    course_id: number
    position: number
    type: string
    name: string
    slug: string
    video: any
    live: any
    live_date: any
    description: string
    content: any
    is_activity: boolean
    limit_date: any
    active: boolean
    updated_at: string
    created_at: string
    id: number
  }

}
