/**
 * タスク一覧表示用の型定義
 */

export interface Task {
  sheet_id: string;
  timestamp: string;
  age: string;
  question: string;
  device: string;
  issue_time: string;
  skill_level: string;
  status: '未着手' | '対応中' | '完了' |'掲載終了' ;
  progress: number;
  comment: string;
  article_url: string;
}

export interface ApiResponse {
  success: boolean;
  message: string;
  tasks: Task[];
  cached_at: string;
  error?: string;
}

export interface StatusColor {
  [key: string]: string;
}
