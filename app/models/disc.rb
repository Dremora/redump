class Disc < ActiveRecord::Base
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
  validates_length_of :internal_serial, :in => 0..255
  validates_length_of :title, :in => 1..255
  validates_length_of :subtitle, :in => 0..255
  validates_length_of :scene_filename, :in => 0..255
  validates_length_of :filename, :in => 0..255
  validates_format_of :internal_date, :with => /(19|20)[0-9][0-9]-[01][0-9](-[0123][0-9])?/, :allow_blank => true
  validates_format_of :size, :with => /[1-9][0-9]{6,13}/, :allow_blank => true
  validates_format_of :crc32, :with => /[0-9A-Fa-f]{8}/, :allow_blank => true
  validates_format_of :md5, :with => /[0-9A-Fa-f]{32}/, :allow_blank => true
  validates_format_of :sha1, :with => /[0-9A-Fa-f]{40}/, :allow_blank => true
end
