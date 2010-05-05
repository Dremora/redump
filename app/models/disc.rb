class Disc < ActiveRecord::Base
  has_many :property_values
  belongs_to :media_type
  
  @@statuses = {
    1 => :not_dumped,
    2 => :dumped,
    3 => :redumped
  }
  
  def self.statuses
    @@statuses
  end
  
  attr_protected :created_at, :updated_at
  
  validates_inclusion_of :status, :in => @@statuses.keys
  validates_length_of :comments, :maximum => 65535
  validates_length_of :internal_serial, :maximum => 255
  validates_length_of :title, :in => 1..255
  validates_length_of :subtitle, :maximum => 255
  validates_length_of :scene_filename, :maximum => 255
  validates_length_of :filename, :maximum => 255
  validates_format_of :internal_date, :with => /\A(19|20)[0-9][0-9]-[01][0-9](-[0123][0-9])?\Z/, :allow_blank => true
  validates_format_of :size, :with => /\A[1-9][0-9]{6,13}\Z/, :allow_blank => true
  validates_format_of :crc32, :with => /\A[0-9A-Fa-f]{8}\Z/, :allow_blank => true
  validates_format_of :md5, :with => /\A[0-9A-Fa-f]{32}\Z/, :allow_blank => true
  validates_format_of :sha1, :with => /\A[0-9A-Fa-f]{40}\Z/, :allow_blank => true
  
  before_save do
    self.comments = self.comments.to_s
    self.internal_serial = self.internal_serial.to_s
    self.internal_date = self.internal_date.to_s
    self.subtitle = self.subtitle.to_s
    self.scene_filename = self.scene_filename.to_s
    self.filename = self.filename.to_s
    self.crc32 = self.crc32.to_s
    self.md5 = self.md5.to_s
    self.sha1 = self.sha1.to_s
    
    self.crc32.downcase!
    self.md5.downcase!
    self.sha1.downcase!
  end
end
